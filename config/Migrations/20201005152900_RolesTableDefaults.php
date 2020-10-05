<?php

use Migrations\AbstractMigration;

class RolesTableDefaults extends AbstractMigration
{

    /**
     * Add defaults to deny_delete and deny_edit
     *
     */
    public function change()
    {
        $this->table('qobo_roles')
            ->changeColumn('deny_delete', 'boolean', ['default' => false ])
            ->changeColumn('deny_edit', 'boolean', ['default' => false ])
            ->save();
    }
}
