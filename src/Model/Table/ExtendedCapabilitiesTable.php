<?php
declare(strict_types=1);

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

use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Capabilities Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Roles
 * @property \Cake\ORM\Association\BelongsToMany $Roles
 */
class ExtendedCapabilitiesTable extends Table
{
    /**
     * Initialize method
     *
     * @param mixed[] $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('qobo_extended_capabilities');
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
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->add('id', 'valid', ['rule' => 'uuid'])
            ->allowEmpty('id', 'create');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['role_id'], 'Roles'));

        $rules->add(function (EntityInterface $entity, array $options) {
            $resource = $entity->get('resource');

            $table = TableRegistry::getTableLocator()->get($resource);

            return true;
        }, 'ValidResource', [
            'errorField' => 'resource',
            'message' => 'Resource does not exist',
        ]);

        $rules->add(function (EntityInterface $entity, array $options) {
            $resource = $entity->get('resource');

            $table = TableRegistry::getTableLocator()->get($resource);

            return $table->hasBehavior('Authorized');
        }, 'ValidResource', [
            'errorField' => 'resource',
            'message' => 'Resource does not have the Authorized behavior enabled',
        ]);

        $rules->add(function (EntityInterface $entity, array $options) {
            $resource = $entity->get('resource');

            $table = TableRegistry::getTableLocator()->get($resource);

            if (!$table->hasBehavior('Authorized')) {
                return false;
            }

            /**
             * @var \RolesCapabilities\Model\Behavior\AuthorizedBehavior
             */
            $authorizedBehavior = $table->getBehavior('Authorized');

            $associations = array_keys($authorizedBehavior->getAssociations());

            return in_array($entity->get('association'), $associations);
        }, 'ValidAssociation', [
            'errorField' => 'association',
            'message' => 'Capabilities association does not exist',
        ]);

        return $rules;
    }
}
