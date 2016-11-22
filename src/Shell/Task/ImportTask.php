<?php
namespace RolesCapabilities\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Task for importing system roles.
 */
class ImportTask extends Shell
{
    /**
     * {@inheritDoc}
     */
    public function main()
    {
        $this->out('Task: import system role(s)');
        $this->hr();

        // get roles table
        $table = TableRegistry::get('RolesCapabilities.Roles');

        $roles = $this->_getSystemRoles();
        if ($roles) {
            foreach ($roles as $role) {
                $entity = $table->newEntity();
                foreach ($role as $k => $v) {
                    $entity->{$k} = $v;
                }

                if ($table->save($entity)) {
                    $msg = 'Role [' . $entity->name . '] imported';
                    // associate imported role with matching group
                    $group = $this->_getGroupByRoleName($entity->name);
                    if ($table->Groups->link($entity, [$group])) {
                        $msg .= ' and associated with group [' . $group->name . ']';
                    }
                    $msg .= ' successfully';

                    $this->out('<info>' . $msg . '</info>');
                } else {
                    $this->err('Failed to import role [' . $entity->name . ']');
                    $errors = $this->_getImportErrors($entity);
                    if (!empty($errors)) {
                        $this->out(implode("\n", $errors));
                        $this->hr();
                    }
                }
            }
        }

        $this->out('<success>System roles(s) importing task completed</success>');
    }

    /**
     * Get system roles.
     *
     * @return string|null
     */
    protected function _getSystemRoles()
    {
        $result = [
            [
                'name' => 'Admins',
                'description' => 'Administrators role',
                'deny_edit' => true,
                'deny_delete' => true
            ],
            [
                'name' => 'Everyone',
                'description' => 'Generic role',
                'deny_edit' => false,
                'deny_delete' => true
            ]
        ];

        if (empty($result)) {
            $this->err('System role(s) are not defined, all following tasks are skipped');
        }

        return $result;
    }

    /**
     * Fetch group entity based on role name. Abort if not found.
     *
     * @param  string $name Role name
     * @return \Cake\ORM\Entity
     */
    protected function _getGroupByRoleName($name)
    {
        $result = TableRegistry::get('Groups.Groups')->findByName($name)->first();

        if (!$result) {
            $this->abort('Failed fetching group [' . $name . '], please make sure it exists.');
        }

        return $result;
    }

    /**
     * Get import errors from entity object.
     *
     * @param  \Cake\ORM\Entity $entity Entity instance
     * @return array
     */
    protected function _getImportErrors($entity)
    {
        $result = [];
        if (!empty($entity->errors())) {
            foreach ($entity->errors() as $field => $error) {
                if (is_array($error)) {
                    $msg = implode(', ', $error);
                } else {
                    $msg = $errors;
                }
                $result[] = $msg . ' [' . $field . ']';
            }
        }

        return $result;
    }
}
