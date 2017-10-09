<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
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
     * Current user details - used to filter list queries
     *
     * @var array
     */
    protected $_currentUser = [];

    /**
     * Group(s) roles
     *
     * @var array
     */
    protected $_groupsRoles = [];

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
     *  getUserRolesEntities()
     *
     * @param array $userRoles  roles assigned to specified user
     * @return array            list of user's roles entities
     */
    public function getUserRolesEntities($userRoles)
    {
        $query = $this->find('list')->where(['role_id IN' => array_keys($userRoles)]);
        $entities = $query->all();
        if (!$entities->isEmpty()) {
            return array_values($entities->toArray());
        }

        return [];
    }

    /**
     *  getUserGroups()
     *
     * @param string $userId    ID of checked user
     * @param bool $accessCheck flag indicated to check permissions or not
     * @return array            user's groups
     */
    public function getUserGroups($userId, $accessCheck = false)
    {
        $userGroups = $this->Roles->Groups->getUserGroups($userId, ['accessCheck' => $accessCheck]);

        return $userGroups;
    }
}
