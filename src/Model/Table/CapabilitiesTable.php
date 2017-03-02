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
use RolesCapabilities\Access\AccessFactory;
use RolesCapabilities\Access\Utils;
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

        $actions = Utils::getActions($controllerName, $actions);

        if (empty($actions)) {
            return $result;
        }

        // get controller table instance
        $controllerTable = Utils::getControllerTableInstance($controllerName);

        return $this->_getCapabilities(
            Utils::generateCapabilityControllerName($controllerName),
            $actions,
            $this->_getTableAssignationFields($controllerTable)
        );
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
            $result[Utils::CAP_TYPE_FULL][] = new Cap(
                Utils::generateCapabilityName($controllerName, $action),
                [
                    'label' => Utils::generateCapabilityLabel($controllerName, $action . '_all'),
                    'description' => Utils::generateCapabilityDescription(
                        $controllerName,
                        Utils::humanizeActionName($action)
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
                $result[Utils::CAP_TYPE_OWNER][] = new Cap(
                    Utils::generateCapabilityName($controllerName, $action . '_' . $assignationField),
                    [
                        'label' => Utils::generateCapabilityLabel($controllerName, $action . '_' . $assignationField),
                        'description' => Utils::generateCapabilityDescription(
                            $controllerName,
                            Utils::humanizeActionName($action) . ' if owner (' . Inflector::humanize($assignationField) . ')'
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
}
