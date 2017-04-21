<?php
namespace RolesCapabilities\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PersonalPermissions Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 *
 * @method \RolesCapabilities\Model\Entity\PersonalPermission get($primaryKey, $options = [])
 * @method \RolesCapabilities\Model\Entity\PersonalPermission newEntity($data = null, array $options = [])
 * @method \RolesCapabilities\Model\Entity\PersonalPermission[] newEntities(array $data, array $options = [])
 * @method \RolesCapabilities\Model\Entity\PersonalPermission|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \RolesCapabilities\Model\Entity\PersonalPermission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \RolesCapabilities\Model\Entity\PersonalPermission[] patchEntities($entities, array $data, array $options = [])
 * @method \RolesCapabilities\Model\Entity\PersonalPermission findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PersonalPermissionsTable extends Table
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

        $this->setTable('personal_permissions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->uuid('id')
            ->allowEmpty('id', 'create');

        $validator
            ->uuid('foreign_key')
            ->requirePresence('foreign_key', 'create')
            ->notEmpty('foreign_key');

        $validator
            ->requirePresence('model', 'create')
            ->notEmpty('model');

        $validator
            ->requirePresence('creator', 'create')
            ->notEmpty('creator');

        $validator
            ->requirePresence('type', 'create')
            ->notEmpty('type');

        $validator
            ->dateTime('expired')
            ->allowEmpty('expired');

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
        return $rules;
    }
}
