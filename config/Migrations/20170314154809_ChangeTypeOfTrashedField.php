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

        // Update existing records to set null for non-deleted ones
        // NOTE: cast to char is needed for the mysql 5.7*!
        $count = $this->execute('UPDATE roles SET trashed=NULL WHERE CAST(trashed AS CHAR(20)) = "0000-00-00 00:00:00"');
    }
}
