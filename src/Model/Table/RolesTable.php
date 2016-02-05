<?php
namespace RolesCapabilities\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use RolesCapabilities\Model\Entity\Role;

/**
 * Roles Model
 *
 * @property \Cake\ORM\Association\BelongsToMany $Capabilities
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

        $this->table('roles');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Capabilities', [
            'foreignKey' => 'role_id',
            'targetForeignKey' => 'capability_id',
            'joinTable' => 'capabilities_roles',
            'className' => 'RolesCapabilities.Capabilities'
        ]);
        $this->belongsToMany('Groups', [
            'foreignKey' => 'role_id',
            'targetForeignKey' => 'group_id',
            'joinTable' => 'groups_roles',
            'className' => 'RolesCapabilities.Groups'
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
}
