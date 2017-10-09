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
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Access\Utils;

/**
 * Task for assigning all capabilities to Admins role.
 */
class AssignTask extends Shell
{
    /**
     * Target role name.
     *
     * @var string
     */
    protected $_role;

    /**
     * Output message.
     *
     * @var string
     */
    protected $_msg = 'Task for assigning all capabilities to [%s] role has been completed';

    /**
     * {@inheritDoc}
     */
    public function main()
    {
        $this->_role = (string)Configure::read('RolesCapabilities.Roles.Admin.name');

        if (empty($this->_role)) {
            $this->abort('[Admins] role is not defined');
        }

        $this->out('Task: assign all capabilities to [' . $this->_role . '] role');
        $this->hr();

        // get roles table
        $table = TableRegistry::get('RolesCapabilities.Roles');

        $role = $this->_getAdminsRoleEntity($table);

        $success = false;
        $count = 0;
        if ($role) {
            $allCapabilities = $this->_getAllCapabilities($table);
            if ($allCapabilities) {
                $count = count($allCapabilities);
                $role->capabilities = $allCapabilities;
                // delete existing role capabilities
                $table->Capabilities->deleteAll(['role_id' => $role->id]);

                // save role with all capabilities assigned to it.
                // bypass validation rules as 'Admins' role is not editable by default.
                $success = $table->save($role, ['checkRules' => false]);
            }
        }

        if ($count) {
            $this->out('<info>[' . $count . '] capabilities have been assigned to [' . $this->_role . '] role</info>');
        }

        $msg = sprintf($this->_msg, $this->_role);
        if ($success) {
            $this->out('<success>' . $msg . '</success>');
        } else {
            $this->out('<warning>' . $msg . '</warning>');
        }
    }

    /**
     * Get 'Admins' role with its capabilities.
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return \Cake\ORM\Entity|null
     */
    protected function _getAdminsRoleEntity(Table $table)
    {
        $result = $table
            ->findByName($this->_role)
            ->first();

        if (!$result) {
            $this->err('[' . $this->_role . '] role was not found in the system, all following tasks are skipped');
        }

        return $result;
    }

    /**
     * Get all capabilities.
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getAllCapabilities(Table $table)
    {
        $result = [];

        $allCapabilities = Utils::getAllCapabilities();
        if (empty($allCapabilities)) {
            $this->err('No capabilities found in the system, all following tasks are skipped');
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
            $this->err('No capabilities found in the system, all following tasks are skipped');
        }

        return $result;
    }
}
