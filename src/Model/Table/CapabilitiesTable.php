<?php
namespace RolesCapabilities\Model\Table;

use Cake\Core\App;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use ReflectionClass;
use ReflectionMethod;
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
     * Current user details
     *
     * @var array
     */
    protected $_currentUser = [];

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
     * Non-assignation actions
     *
     * @var array
     */
    protected $_nonAssignationActions = [
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
     * Current user info setter.
     *
     * @param array $user
     */
    public function setCurrentUser(array $user = [])
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
     * User action capability setter.
     *
     * @param string                        $type       Capability type
     * @param \RolesCapabilities\Capability $capability Capability instance
     */
    public function setUserActionCapability($type, Cap $capability)
    {
        $this->_userActionCapabilities[$type][] = $capability;
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
     * @param  array  $subject Subject
     * @param  array  $user User
     * @return void
     * @throws Cake\Network\Exception\ForbiddenException
     * @todo                 this needs re-thinking
     */
    public function checkAccess(array $subject, $user)
    {
        $plugin = is_null($subject['plugin']) ? 'App' : $subject['plugin'];
        $controllerName = App::className($plugin . '.' . $subject['controller'] . 'Controller', 'Controller');
        $capability = $this->generateCapabilityName(
            $this->generateCapabilityControllerName($controllerName),
            $subject['action']
        );
        $allCapabilities = $this->getCapabilities($controllerName);
        $capExists = false;
        foreach ($allCapabilities as $cap) {
            if ($cap->getName() === $capability) {
                $capExists = true;
                break;
            }
        }

        $hasAccess = false;
        if ($capExists) {
            if ($this->hasAccess($capability, $user['id'])) {
                $hasAccess = true;
            } else {
                $hasAccess = false;
            }
        } else {
            /*
            if capability does not exist user is allowed access
             */
            $hasAccess = true;
        }

        /*
        superuser has access everywhere
         */
        if ($user['is_superuser']) {
            $hasAccess = true;
        }
        if (!$hasAccess) {
            throw new ForbiddenException();
        }
    }

    /**
     * Returns Controller permission capabilities.
     *
     * @param  string $controllerName Controller name
     * @param  array  $actions        Controller actions
     * @return array
     */
    public function getCapabilities($controllerName, array $actions = [])
    {
        $result = [];

        if (!is_string($controllerName)) {
            return $result;
        }

        $skipControllers = $controllerName::getSkipControllers();
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
        $skipActions = array_merge(
            $controllerName::getSkipActions($controllerName),
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
        foreach ($actions as $action) {
            // generate action's full (all) type capabilities
            $result[static::CAP_TYPE_FULL][] = new Cap(
                $this->generateCapabilityName($controllerName, $action),
                [
                    'label' => $this->generateCapabilityLabel($controllerName, $action  . '_all'),
                    'description' => $this->generateCapabilityDescription(
                        $controllerName,
                        $action . ' to all'
                    )
                ]
            );
            // skip rest of the logic if assignation fields are not found
            // or if current action does not support assignment (Example: add / create)
            if (empty($assignationFields) || in_array($action, $this->_nonAssignationActions)) {
                continue;
            }

            // generate action's owner (assignation field) type capabilities
            foreach ($assignationFields as $assignationField) {
                $result[static::CAP_TYPE_OWNER][] = new Cap(
                    $this->generateCapabilityName($controllerName, $action . '_' . $assignationField),
                    [
                        'label' => $this->generateCapabilityLabel($controllerName, $action . '_' . $assignationField),
                        'description' => $this->generateCapabilityDescription(
                            $controllerName,
                            $action . ' if owner (' . $assignationField . ')'
                        ),
                        'field' => $assignationField
                    ]
                );
            }
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
        $userGroups = $this->Roles->Groups->getUserGroups($userId);

        $userRoles = [];
        if (!empty($userGroups)) {
            $userRoles = $this->getGroupsRoles($userGroups);
        }

        $userCaps = [];
        if (!empty($userRoles)) {
            $query = $this->find('list')->where(['role_id IN' => array_keys($userRoles)]);
            $userCaps = $query->toArray();
        }

        return array_values($userCaps);
    }

    /**
     * Method that retrieves specified group(s) roles.
     * @param  array  $userGroups group(s) id(s)
     * @return array
     */
    public function getGroupsRoles(array $userGroups = [])
    {
        $result = [];

        if (!empty($userGroups)) {
            $query = $this->Roles->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ]);
            $query->matching('Groups', function ($q) use ($userGroups) {
                return $q->where(['Groups.id IN' => array_keys($userGroups)]);
            });
            $result = $query->toArray();
        }

        return $result;
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
