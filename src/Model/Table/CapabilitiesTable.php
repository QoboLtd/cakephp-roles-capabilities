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

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Capabilities Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Roles
 * @property \Cake\ORM\Association\BelongsToMany $Roles
 */
class CapabilitiesTable extends Table
{
    /**
     * Group(s) roles
     *
     * @var array
     */
    protected $_groupsRoles = [];

    /**
     * Initialize method
     *
     * @param mixed[] $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('qobo_capabilities');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
            'className' => 'RolesCapabilities.Roles',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
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
    public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->existsIn(['role_id'], 'Roles'));

        return $rules;
    }

    /**
     * Method that retrieves specified group(s) roles.
     *
     * @param  mixed[]  $userGroups group(s) id(s)
     * @return mixed[]
     */
    public function getGroupsRoles(array $userGroups = []): array
    {
        $key = implode('.', $userGroups);

        if (!empty($this->_groupsRoles[$key])) {
            return $this->_groupsRoles[$key];
        }

        $result = [];

        if (!empty($userGroups)) {
            $query = $this->Roles->find('list', [
                'keyField' => 'id',
                'valueField' => 'name',
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
     * @param mixed[] $userRoles  roles assigned to specified user
     * @return mixed[]            list of user's roles entities
     */
    public function getUserRolesEntities(array $userRoles): array
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
     * @return mixed[]            user's groups
     */
    public function getUserGroups(string $userId, bool $accessCheck = false): array
    {
        $userGroups = $this->Roles->Groups->getUserGroups($userId, ['accessCheck' => $accessCheck]);

        return $userGroups;
    }
}
