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
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Access\Utils;

/**
 * Assign Task
 *
 * Assign all capabilities to given role, like Admins.
 */
class AssignTask extends Shell
{
    /**
     * @var string $role Target role name
     */
    protected $role;

    /**
     * Main task method
     *
     * @return bool True on success, false otherwise
     */
    public function main()
    {
        $this->info('Task: assign all capabilities to the admin role');
        $this->hr();

        // Read admin role from configuration
        $this->role = (string)Configure::read('RolesCapabilities.Roles.Admin.name');
        if (empty($this->role)) {
            $this->warn('[Admins] role is not configured.  Nothing to do.');

            return true;
        }

        $this->info('Configured admin role is [' . $this->role . ']');

        // get roles table
        $table = TableRegistry::get('RolesCapabilities.Roles');

        $role = $this->getAdminsRoleEntity($table);
        if (empty($role)) {
            $this->abort('[' . $this->role . '] is not found in the system!');
        }

        $allCapabilities = $this->getAllCapabilities($table);
        $count = count($allCapabilities);
        $role->capabilities = $allCapabilities;
        // delete existing role capabilities
        $table->Capabilities->deleteAll(['role_id' => $role->id]);

        // save role with all capabilities assigned to it.
        // bypass validation rules as 'Admins' role is not editable by default.
        $success = $table->save($role, ['checkRules' => false]);
        if (!$success) {
            $this->abort("Failed to assign [$count] capabilities to role [" . $this->role . "]");
        }

        $this->info('[' . $count . '] capabilities have been assigned to [' . $this->role . '] role');
    }

    /**
     * Get 'Admins' role with its capabilities.
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return \Cake\ORM\Entity|null
     */
    protected function getAdminsRoleEntity(Table $table)
    {
        $result = $table
            ->findByName($this->role)
            ->first();

        return $result;
    }

    /**
     * Get all capabilities.
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function getAllCapabilities(Table $table)
    {
        $result = [];

        $allCapabilities = Utils::getAllCapabilities();
        if (empty($allCapabilities)) {
            $this->abort('No capabilities at all found in the system!');
        }

        foreach ($allCapabilities as $groupName => $groupCaps) {
            if (empty($groupCaps)) {
                continue;
            }

            foreach ($groupCaps as $type => $caps) {
                foreach ($caps as $cap) {
                    $result = array_merge($result, [$cap->getName()]);
                }
            }
        }

        // set all capabilities as selected
        $result = array_fill_keys($result, '1');
        $result = $table->prepareCapabilities($result);

        if (empty($result)) {
            $this->abort('No capabilities found in the system!');
        }

        return $result;
    }
}
