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
use RolesCapabilities\Model\Entity\Permission;
use Webmozart\Assert\Assert;

/**
 * Permissions Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 *
 * @method \RolesCapabilities\Model\Entity\Permission get($primaryKey, $options = [])
 * @method \RolesCapabilities\Model\Entity\Permission newEntity($data = null, array $options = [])
 * @method \RolesCapabilities\Model\Entity\Permission[] newEntities(array $data, array $options = [])
 * @method \RolesCapabilities\Model\Entity\Permission|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \RolesCapabilities\Model\Entity\Permission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \RolesCapabilities\Model\Entity\Permission[] patchEntities($entities, array $data, array $options = [])
 * @method \RolesCapabilities\Model\Entity\Permission findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PermissionsTable extends Table
{
    /**
     * Allowed permissions actions
     */
    const ALLOWED_ACTIONS = ['view', 'edit', 'delete'];

    const ALLOWED_OWNER_MODELS = ['Groups', 'Users'];

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('qobo_permissions');
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
            ->uuid('owner_foreign_key')
            ->requirePresence('owner_foreign_key', 'create')
            ->notEmpty('owner_foreign_key');

        $validator
            ->requirePresence('owner_model', 'create')
            ->inList('owner_model', self::ALLOWED_OWNER_MODELS)
            ->notEmpty('owner_model');

        $validator
            ->requirePresence('model', 'create')
            ->notEmpty('model');

        $validator
            ->uuid('creator')
            ->requirePresence('creator', 'create')
            ->notEmpty('creator');

        $validator
            ->requirePresence('type', 'create')
            ->inList('type', self::ALLOWED_ACTIONS)
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

    /**
     * Retrieves view permission for specified user.
     *
     * @param string $modelName Model name
     * @param string $foreignKey Foreign key
     * @param string $userId User ID
     * @return \RolesCapabilities\Model\Entity\Permission|null
     */
    public function fetchUserViewPermission(string $modelName, string $foreignKey, string $userId): ?Permission
    {
        Assert::stringNotEmpty($modelName);
        Assert::uuid($foreignKey);
        Assert::uuid($userId);

        $entity = $this->find()
            ->where([
                'owner_model' => 'Users',
                'model' => $modelName,
                'owner_foreign_key' => $userId,
                'foreign_key' => $foreignKey,
                'type' => 'view',
            ])
            ->first();

        Assert::nullOrIsInstanceOf($entity, Permission::class);

        return $entity;
    }
}
