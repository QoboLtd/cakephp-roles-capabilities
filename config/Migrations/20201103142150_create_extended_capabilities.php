<?php

use Migrations\AbstractMigration;

class CreateExtendedCapabilities extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('qobo_extended_capabilities', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [ 'null' => false ])
            ->addColumn('role_id', 'uuid', [ 'null' => false ])
            ->addColumn('resource', 'string', [ 'null' => false ])
            ->addColumn('operation', 'string', [ 'null' => false ])
            ->addColumn('association', 'string', [ 'null' => false ])
            ->addIndex(['role_id', 'resource', 'operation', 'association' ], ['unique' => true ])
            ->addIndex(['role_id'], [ 'name' => 'idx_extended_capabilities_role', 'unique' => false ])
            ->addForeignKey('role_id', 'qobo_roles', 'id', [ 'delete' => 'CASCADE' ])
            ->create();
    }
}
