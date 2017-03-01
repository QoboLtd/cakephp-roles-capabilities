<?php
namespace RolesCapabilities\Model\Table;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use ReflectionClass;
use ReflectionMethod;
use RolesCapabilities\Access\AccessFactory;
use RolesCapabilities\Capability as Cap;
use RolesCapabilities\Model\Entity\Capability;

/**
 * Capabilities Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Roles
 * @property \Cake\ORM\Association\BelongsToMany $Roles
 */
class CapabilitiesTable extends Table
{
    /**
     * Full type capability identifier
     */
    const CAP_TYPE_FULL = 'full';

    /**
     * Owner type capability identifier
     */
    const CAP_TYPE_OWNER = 'owner';

    /**
     * Default skip controllers
     *
     * @var array
     */
    protected $_skipControllers = [
        'CakeDC\Users\Controller\SocialAccountsController',
        'App\Controller\PagesController'
    ];

    /**
     * Default skip actions
     *
     * @var array
     */
    protected $_skipActions = [
        '*' => ['getCapabilities', 'getSkipControllers', 'getSkipActions']
    ];

    /**
     * Current request parameters
     *
     * @var array
     */
    protected $_currentRequest;

    /**
     * Current user details
     *
     * @var array
     */
    protected $_currentUser = [];

    /**
     * All user capabilities
     *
     * @var array
     */
    protected $_userCapabilities = [];

    /**
     * Controller action(s) capabilities
     *
     * @var array
     */
    protected $_controllerActionCapabilites = [];

    /**
     * Group(s) roles
     *
     * @var array
     */
    protected $_groupsRoles = [];

    /**
     * User action specific capabilities
     *
     * @var array
     */
    protected $_userActionCapabilities = [];

    /**
     * Models that hold user information, usually used as associations to relate records to a user.
     *
     * @var array
     */
    protected $_assignationModels = [
        'Users',
        'CakeDC/Users.Users'
    ];

    /**
     * Non-assigned actions
     *
     * @var array
     */
    protected $_nonAssignedActions = [
        'add'
    ];

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        // merge controllers to be skipped from app's configuration
        $skipControllers = Configure::read('RolesCapabilities.skip_controllers');
        $this->_skipControllers = array_merge(
            $this->_skipControllers,
            is_null($skipControllers) ? [] : $skipControllers
        );

        $this->table('capabilities');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
            'className' => 'RolesCapabilities.Roles'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'uuid'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['role_id'], 'Roles'));

        return $rules;
    }

    /**
     * Get full type capability identifier.
     *
     * @return string
     */
    public function getTypeFull()
    {
        return static::CAP_TYPE_FULL;
    }

    /**
     * Get owner type capability identifier.
     *
     * @return string
     */
    public function getTypeOwner()
    {
        return static::CAP_TYPE_OWNER;
    }

    /**
     * Current request parameters setter.
     *
     * @param  array $request Request parameters
     * @return void
     */
    public function setCurrentRequest(array $request)
    {
        $this->_currentRequest = $request;
    }

    /**
     * Current request parameters getter.
     *
     * @param  string|null       $key Specific field to retrieve
     * @return array|string|null
     */
    public function getCurrentRequest($key = null)
    {
        if (!is_null($key)) {
            return isset($this->_currentRequest[$key]) ? $this->_currentRequest[$key] : null;
        }

        return $this->_currentRequest;
    }

    /**
     * Current user info setter.
     *
     * @param  array|null $user User information
     * @return void
     */
    public function setCurrentUser($user)
    {
        $this->_currentUser = $user;
    }

    /**
     * Current user info getter.
     *
     * @param  string|null       $key Specific field to retrieve
     * @return array|string|null
     */
    public function getCurrentUser($key = null)
    {
        if (!is_null($key)) {
            return isset($this->_currentUser[$key]) ? $this->_currentUser[$key] : null;
        }

        return $this->_currentUser;
    }

    /**
     * Returns Controller's class name namespaced.
     *
     * @param array $url array of URL parameters.
     * @return string
     */
    public function getControllerFullName(array $url)
    {
        $result = null;

        if (empty($url['controller'])) {
            return $result;
        }

        $class = $url['controller'];
        if (!empty($url['plugin'])) {
            $class = $url['plugin'] . '.' . $class;
        }
        $result = App::className($class . 'Controller', 'Controller');

        return $result;
    }

    /**
     * User action capability setter.
     *
     * @param  string                        $plugin     Plugin name
     * @param  string                        $controller Controller name
     * @param  string                        $action     Action type
     * @param  string                        $type       Capability type
     * @param  \RolesCapabilities\Capability $capability Capability instance
     * @return void
     */
    public function setUserActionCapability($plugin, $controller, $action, $type, Cap $capability)
    {
        $this->_userActionCapabilities[$plugin][$controller][$action][$type][] = $capability;
    }

    /**
     * User action capabilities getter.
     *
     * @return array
     */
    public function getUserActionCapabilities()
    {
        return $this->_userActionCapabilities;
    }

    /**
     * Check if current user has access to perform action.
     *
     * @param array $url Url
     * @param array|null $user User
     * @return void
     * @throws Cake\Network\Exception\ForbiddenException
     * @todo                 this needs re-thinking
     */
    public function checkAccess(array $url, $user)
    {
        $accessFactory = new AccessFactory();

        return $accessFactory->hasAccess($url, $user);
    }

    /**
     * Method that checks if user has full access on Controller's action.
     *
     * @param  string $type               Capability type
     * @param  array  $actionCapabilities Action capabilities
     * @param  array  $user               User info
     * @param  array  $url                Controller url
     * @return bool
     */
    public function hasTypeAccess($type, array $actionCapabilities, array $user, array $url)
    {
        // skip if action has no access capabilities for specified type
        if (!isset($actionCapabilities[$type])) {
            return false;
        }

        foreach ($actionCapabilities[$type] as $actionCapability) {
            // user has access
            if ($this->hasAccess($actionCapability->getName(), $user['id'])) {
                // store in user's action capabilities
                $this->setUserActionCapability(
                    $url['plugin'],
                    $url['controller'],
                    $url['action'],
                    $type,
                    $actionCapability
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Returns Controller permission capabilities.
     *
     * @param  string $controllerName Controller name
     * @param  array  $actions        Controller actions
     * @return array
     */
    public function getCapabilities($controllerName = null, array $actions = [])
    {
        $result = [];

        if (is_null($controllerName) || !is_string($controllerName)) {
            return $result;
        }

        $skipControllers = [];
        if (is_callable([$controllerName, 'getSkipControllers'])) {
            $skipControllers = $controllerName::getSkipControllers();
        }

        if (in_array($controllerName, $skipControllers)) {
            return $result;
        }

        $actions = $this->_getActions($controllerName, $actions);

        if (empty($actions)) {
            return $result;
        }

        // get controller table instance
        $controllerTable = $this->_getControllerTableInstance($controllerName);

        return $this->_getCapabilities(
            $this->generateCapabilityControllerName($controllerName),
            $actions,
            $this->_getTableAssignationFields($controllerTable)
        );
    }

    /**
     * Method that filters and returns Controller action(s) that can be used for generating access capabilities.
     *
     * @param  string $controllerName Controller name
     * @param  array  $actions        Action(s) to filter. If not specified all controller's public methods will be used.
     * @return array
     */
    protected function _getActions($controllerName, array $actions = [])
    {
        $publicMethods = $this->_getControllerPublicMethods($controllerName);
        // return if controller has no public methods
        if (empty($publicMethods)) {
            return [];
        }

        // if no actions defined, use controller's public methods
        if (!empty($actions)) {
            $actions = array_intersect($actions, $publicMethods);
        } else { // else use controller's public methods
            $actions = $publicMethods;
        }

        if (empty($actions)) {
            return $actions;
        }

        // filter out skipped actions
        $actions = $this->_filterSkippedActions($controllerName, $actions);

        return $actions;
    }

    /**
     * Method that retrieves and returns Controller public methods.
     *
     * @param  string $controllerName Controller name
     * @return array
     */
    protected function _getControllerPublicMethods($controllerName)
    {
        $actions = [];
        $refClass = new ReflectionClass($controllerName);
        foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $actions[] = $method->name;
        }

        return $actions;
    }

    /**
     * Method that filter's out skipped actions from Controller's actions list.
     *
     * @param  string $controllerName Controller name
     * @param  array  $actions        Controller actions
     * @return array
     */
    protected function _filterSkippedActions($controllerName, array $actions)
    {
        $skipActions = [];
        if (is_callable([$controllerName, 'getSkipActions'])) {
            $skipActions = $controllerName::getSkipActions($controllerName);
        }

        $skipActions = array_merge(
            $skipActions,
            $this->getCakeControllerActions()
        );

        foreach ($actions as $k => $action) {
            if (in_array($action, $skipActions)) {
                unset($actions[$k]);
            }
        }

        return $actions;
    }

    /**
     * Method that generates capabilities for specified controller's actions.
     * Capabilities included are full or owner access types.
     *
     * @param  string $controllerName    Controller name
     * @param  array  $actions           Controller actions
     * @param  array  $assignationFields Table assignation fields (example: assigned_to)
     * @return array
     */
    protected function _getCapabilities($controllerName, array $actions, array $assignationFields = [])
    {
        $key = implode('.', $actions);
        if (!empty($this->_controllerActionCapabilites[$controllerName][$key])) {
            return $this->_controllerActionCapabilites[$controllerName][$key];
        }

        $result = [];
        foreach ($actions as $action) {
            // generate action's full (all) type capabilities
            $result[static::CAP_TYPE_FULL][] = new Cap(
                $this->generateCapabilityName($controllerName, $action),
                [
                    'label' => $this->generateCapabilityLabel($controllerName, $action . '_all'),
                    'description' => $this->generateCapabilityDescription(
                        $controllerName,
                        $this->_humanizeActionName($action)
                    )
                ]
            );
            // skip rest of the logic if assignment fields are not found
            // or if current action does not support assignment (Example: add / create)
            if (empty($assignationFields) || in_array($action, $this->_nonAssignedActions)) {
                continue;
            }

            // generate action's owner (assignment field) type capabilities
            foreach ($assignationFields as $assignationField) {
                $result[static::CAP_TYPE_OWNER][] = new Cap(
                    $this->generateCapabilityName($controllerName, $action . '_' . $assignationField),
                    [
                        'label' => $this->generateCapabilityLabel($controllerName, $action . '_' . $assignationField),
                        'description' => $this->generateCapabilityDescription(
                            $controllerName,
                            $this->_humanizeActionName($action) . ' if owner (' . Inflector::humanize($assignationField) . ')'
                        ),
                        'field' => $assignationField
                    ]
                );
            }
        }

        $this->_controllerActionCapabilites[$controllerName][$key] = $result;

        return $this->_controllerActionCapabilites[$controllerName][$key];
    }

    /**
     * Convert action/method name to human-friendly description
     *
     * Action/method names mostly follow CakePHP naming conventions
     * and are not very human-friendly.  For example, 'list' is much
     * less confusing than 'index'.
     *
     * When used in the capability description, an additional layer of
     * confusion is introduced.  For example, 'Allow info' or 'Allow
     * changelog'.  Adjusting these to 'Allow view info' and 'Allo
     * view changelog' help a great deal.
     *
     * @todo Allow controllers to take control over these
     *
     * @param string $action Action/method name to humanize
     * @return string
     */
    protected function _humanizeActionName($action)
    {
        // cameCaseMethod -> under_score -> Human Form -> lowercase
        $result = strtolower(Inflector::humanize(Inflector::underscore($action)));

        switch ($action) {
            case 'index':
                $result = 'list';
                break;
            case 'info':
            case 'changelog':
                $result = 'view ' . $action;
                break;
        }

        return $result;
    }

    /**
     * Method that returns Table instance of specified controller.
     *
     * @param  string          $controllerName Controller name
     * @return \Cake\ORM\Table
     */
    protected function _getControllerTableInstance($controllerName)
    {
        $parts = explode('\\', $controllerName);
        // get last part, "/ArticlesController"
        $tableName = array_pop($parts);
        // remove "Controller" suffix from "/ArticlesController"
        $tableName = str_replace('Controller', '', $tableName);
        // remove "/Controller/" part
        array_pop($parts);
        // get plugin part "/MyPlugin/"
        $plugin = array_pop($parts);
        // prefix plugin to table name if is not "App"
        if ('App' !== $plugin) {
            $tableName = $plugin . '.' . $tableName;
        }

        return TableRegistry::get($tableName);
    }

    /**
     * Method that retrieves and returns Table's assignation fields. These are fields
     * that dictate assigment, usually foreign key associated with a Users tables. (example: assigned_to)
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getTableAssignationFields(Table $table)
    {
        $fields = [];
        foreach ($table->associations() as $association) {
            // skip non-assignation models
            if (!in_array($association->className(), $this->_assignationModels)) {
                continue;
            }

            $fields[] = $association->foreignKey();
        }

        return $fields;
    }

    /**
     * Method that checks if current user is allowed access.
     * Returns true if current user has access, false otherwise.
     * @param  string $capability capability name
     * @param  string $userId     user id
     * @return bool
     */
    public function hasAccess($capability, $userId)
    {
        $userCaps = $this->getUserCapabilities($userId);
        if (in_array($capability, $userCaps)) {
            return true;
        }

        return false;
    }

    /**
     * Method that checks if specified role is allowed access.
     * Returns true if role has access, false otherwise.
     *
     * @param  string $roleId role id
     * @param  string $userId user id
     * @return bool
     */
    public function hasRoleAccess($roleId, $userId)
    {
        if (is_null($roleId)) {
            return true;
        }

        $userGroups = $this->Roles->Groups->getUserGroups($userId);
        $userRoles = [];
        if (!empty($userGroups)) {
            $userRoles = $this->getGroupsRoles($userGroups);
        }

        if (in_array($roleId, array_keys($userRoles))) {
            return true;
        }

        return false;
    }

    /**
     * Get list of skipped controllers.
     *
     * @return array
     */
    public function getSkipControllers()
    {
        return $this->_skipControllers;
    }

    /**
     * Get list of controller's skipped actions.
     *
     * @param  string $controllerName Controller name
     * @return array
     */
    public function getSkipActions($controllerName)
    {
        if (!isset($this->_skipActions[$controllerName])) {
            $result = $this->_skipActions['*'];
        } else {
            $result = $this->_skipActions[$controllerName];
        }

        return $result;
    }

    /**
     * Get list of Cake's Controller class methods.
     *
     * @return array
     */
    public function getCakeControllerActions()
    {
        $result = get_class_methods('Cake\Controller\Controller');

        return $result;
    }

    /**
     * Method that retrieves specified user's capabilities
     * @param  string $userId user id
     * @return array
     */
    public function getUserCapabilities($userId)
    {
        if (!empty($this->_userCapabilities)) {
            return $this->_userCapabilities;
        }

        $userGroups = $this->Roles->Groups->getUserGroups($userId, ['accessCheck' => false]);
        if (empty($userGroups)) {
            return $this->_userCapabilities;
        }

        $userRoles = $this->getGroupsRoles($userGroups);
        if (empty($userRoles)) {
            return $this->_userCapabilities;
        }

        $query = $this->find('list')->where(['role_id IN' => array_keys($userRoles)]);
        $entities = $query->all();
        if (!$entities->isEmpty()) {
            $this->_userCapabilities = array_values($entities->toArray());
        }

        return $this->_userCapabilities;
    }

    /**
     * Method that retrieves specified group(s) roles.
     *
     * @param  array  $userGroups group(s) id(s)
     * @return array
     */
    public function getGroupsRoles(array $userGroups = [])
    {
        $key = implode('.', $userGroups);

        if (!empty($this->_groupsRoles[$key])) {
            return $this->_groupsRoles[$key];
        }

        $result = [];

        if (!empty($userGroups)) {
            $query = $this->Roles->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ]);
            $query->matching('Groups', function ($q) use ($userGroups) {
                return $q->where(['Groups.id IN' => array_keys($userGroups)])->applyOptions(['accessCheck' => false]);
            });
            $result = $query->toArray();
        }

        $this->_groupsRoles[$key] = $result;

        return $this->_groupsRoles[$key];
    }

    /**
     * Generate capability's controller name.
     *
     * @param  string $controllerName Controller name
     * @return string
     */
    public function generateCapabilityControllerName($controllerName)
    {
        $result = str_replace('\\', '_', $controllerName);

        return $result;
    }

    /**
     * Generate capability name.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    public function generateCapabilityName($controllerName, $action)
    {
        $result = 'cap__' . $controllerName . '__' . $action;

        return $result;
    }

    /**
     * Generate capability label.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    public function generateCapabilityLabel($controllerName, $action)
    {
        $result = 'Cap ' . $controllerName . ' ' . $action;

        return $result;
    }

    /**
     * Generate capability description.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    public function generateCapabilityDescription($controllerName, $action)
    {
        $result = 'Allow ' . $action;

        return $result;
    }
}
