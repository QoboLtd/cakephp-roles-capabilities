<?php
use Migrations\AbstractMigration;

class CreatePermissionsTable extends AbstractMigration
{
    public $autoId = false;
    
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('permissions');
        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('foreign_key', 'char', [
            'default' => null,
            'null' => false,
            'limit' => 36,
        ]);
        $table->addColumn('model', 'string', [
            'default' => null,
            'null' => false,
            'limit' => 128,
        ]);
        $table->addColumn('user_id', 'char', [
            'default' => null,
            'null' => false,
            'limit' => 36
        ]);
        $table->addColumn('creator', 'string', [
            'default' => null,
            'null' => false,
            'limit' => 36,
        ]);
        $table->addColumn('type', 'string', [
            'default' => null,
            'null' => false,
            'limit' => 15,
        ]);
        $table->addColumn('is_active', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('expired', 'datetime', [
            'default' => null,
            'null' => true
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addPrimaryKey([
            'id',
        ]);

        $table->create();
    }
}
