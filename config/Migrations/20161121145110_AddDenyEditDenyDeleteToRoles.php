<?php
use Migrations\AbstractMigration;

class AddDenyEditDenyDeleteToRoles extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('roles');
        $table->addColumn('deny_edit', 'boolean', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('deny_delete', 'boolean', [
            'default' => null,
            'null' => false,
        ]);
        $table->update();
    }
}
