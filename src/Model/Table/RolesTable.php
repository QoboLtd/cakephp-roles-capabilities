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
use RolesCapabilities\EntityAccess\Operation;

/**
 * Roles Model
 *
 * @property \Cake\ORM\Association\HasMany $ExtendedCapabilities
 * @property \Cake\ORM\Association\BelongsToMany $Groups
 */
class RolesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('qobo_roles');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Muffin/Trash.Trash');
        $this->addBehavior('RolesCapabilities.Authorized', [
            'associations' => [
                'AssignedRoles' => [ 'association' => 'Groups.Users' ],
            ],
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'AssignedRoles' ],
            ],
        ]);

        $this->hasMany('ExtendedCapabilities', [
            'foreignKey' => 'role_id',
            'className' => 'RolesCapabilities.ExtendedCapabilities',
            'dependent' => true,
        ]);

        $this->belongsToMany('Groups', [
            'foreignKey' => 'role_id',
            'targetForeignKey' => 'group_id',
            'joinTable' => 'groups_roles',
            'className' => 'Groups.Groups',
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
            ->notEmpty('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

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
        $rules->add($rules->isUnique(['name']));

        // don't allow editing of non-editable role(s)
        $rules->addUpdate(function ($entity, $options) {
            return ! $entity->getOriginal('deny_edit');
        }, 'systemCheck');

        // don't allow deletion of non-deletable role(s)
        $rules->addDelete(function ($entity, $options) {
            return !$entity->deny_delete;
        }, 'systemCheck');

        return $rules;
    }
}
