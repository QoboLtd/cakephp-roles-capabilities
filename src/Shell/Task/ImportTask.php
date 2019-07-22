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
namespace RolesCapabilities\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Webmozart\Assert\Assert;

/**
 * Import Task
 *
 * Import system roles.
 */
class ImportTask extends Shell
{
    /**
     * Main task method
     *
     * @return bool True on success, false otherwise
     */
    public function main()
    {
        $this->info('Task: import system roles');
        $this->hr();

        $roles = Configure::read('RolesCapabilities.Roles');
        if (empty($roles)) {
            $this->warn("No roles configured for importing.  Nothing to do.");

            return true;
        }

        /**
         * @var \RolesCapabilities\Model\Table\RolesTable $table
         */
        $table = TableRegistry::get('RolesCapabilities.Roles');

        foreach ($roles as $role) {
            if (empty($role['name'])) {
                $this->warn("Skipping role without a name.");
                continue;
            }

            $entity = $table->find()->where(['name' => $role['name']])->contain(['Groups' => function ($q) {
                return $q->select(['Groups.id']);
            }])->first();

            Assert::nullOrIsInstanceOf($entity, EntityInterface::class);

            $linkedGroups = null === $entity ? [] : array_map(function ($item) {
                return $item->get('id');
            }, $entity->get('groups'));

            if (null !== $entity && $entity->get('deny_edit')) {
                $this->warn(sprintf('Roles "%s" already exists and is not allowed to be modified.', $role['name']));
                continue;
            }

            null === $entity ?
                $this->info(sprintf('Creating role "%s".', $role['name'])) :
                $this->info(sprintf('Updating role "%s".', $role['name']));

            $entity = null === $entity ? $table->newEntity() : $entity;
            $entity = $table->patchEntity($entity, $role);

            if (! $table->save($entity)) {
                $this->err("Errors: \n" . implode("\n", $this->getImportErrors($entity)));
                $this->abort("Failed to create role [" . $entity->get('name') . "]");
            }

            $group = $this->getGroupByRoleName($entity->get('name'));

            if (null === $group || in_array($group->get('id'), $linkedGroups, true)) {
                continue;
            }

            // associate imported role with matching group
            if ($table->Groups->link($entity, [$group])) {
                $this->info(sprintf('Role "%s" linked with group "%s"', $entity->get('name'), $group->get('name')));
            }
        }

        $this->success('System roles imported successfully');
    }

    /**
     * Fetch group entity based on role name
     *
     * @param  string $name Role name
     * @return \Cake\Datasource\EntityInterface|null
     */
    protected function getGroupByRoleName(string $name) : ?EntityInterface
    {
        $result = TableRegistry::get('Groups.Groups')->find()
            ->enableHydration(true)
            ->where(['name' => $name])
            ->first();

        Assert::nullOrIsInstanceOf($result, EntityInterface::class);

        return $result;
    }

    /**
     * Get import errors from entity object.
     *
     * @param  \Cake\Datasource\EntityInterface $entity Entity instance
     * @return mixed[]
     */
    protected function getImportErrors(EntityInterface $entity): array
    {
        $result = [];

        if (empty($entity->getErrors())) {
            return $result;
        }

        foreach ($entity->getErrors() as $field => $error) {
            if (is_array($error)) {
                $msg = implode(', ', $error);
            } else {
                $msg = $error;
            }
            $result[] = $msg . ' [' . $field . ']';
        }

        return $result;
    }
}
