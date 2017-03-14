<?php
use Migrations\AbstractMigration;

class ChangeTypeOfTrashedField extends AbstractMigration
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
        $table->changeColumn('trashed', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->save();
    }
}
