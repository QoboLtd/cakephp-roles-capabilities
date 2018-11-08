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

            if ($table->exists(['name' => $role['name']])) {
                $this->warn("Role [" . $role['name'] . "] already exists. Skipping.");
                continue;
            }

            $this->info("Role [" . $role['name'] . "] does not exist. Creating.");
            $entity = $table->newEntity();
            /**
             * @var \RolesCapabilities\Model\Entity\Role $entity
             */
            $entity = $table->patchEntity($entity, $role);

            $group = $this->getGroupByRoleName($entity->name);
            if (empty($group)) {
                $this->abort("Failed to fetch group [" . $entity->name . "]");
            }

            $result = $table->save($entity);
            if (!$result) {
                $this->err("Errors: \n" . implode("\n", $this->getImportErrors($entity)));
                $this->abort("Failed to create role [" . $entity->name . "]");
            }

            $msg = 'Role [' . $entity->name . '] imported';
            // associate imported role with matching group
            if ($table->Groups->link($entity, [$group])) {
                $msg .= ' and associated with group [' . $group->name . ']';
            }
            $msg .= ' successfully';

            $this->info($msg);
        }

        $this->success('System roles imported successfully');
    }

    /**
     * Fetch group entity based on role name
     *
     * @param  string $name Role name
     * @return \Cake\Datasource\EntityInterface|null
     */
    protected function getGroupByRoleName(string $name)
    {
        $result = TableRegistry::get('Groups.Groups')->findByName($name)->first();

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
