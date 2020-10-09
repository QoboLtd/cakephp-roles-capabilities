<?php
use Migrations\AbstractMigration;

class AddIndexToCapabilities extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        // unless there is a bug that left orphan capabilities due to missing FK then this shouldn't delete anything.
        $this->execute('DELETE FROM qobo_capabilities WHERE role_id NOT IN (SELECT id FROM qobo_roles);');

        $table = $this->table('qobo_capabilities');

        $table->addIndex([
            'role_id',
        ], [
            'name' => 'idx_role_id',
            'unique' => false,
        ]);

        $table->addForeignKey('role_id', 'qobo_roles', 'id');

        $table->update();
    }
}
