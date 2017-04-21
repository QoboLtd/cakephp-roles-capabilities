<?php
use Migrations\AbstractMigration;

class AddGroups extends AbstractMigration
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
        $table = $this->table('permissions');
        
        $table->addColumn('owner_model', 'string', [
            'default' => null,
            'null' => false,
            'limit' => 50,
        ]);

        $table->removeColumn('user_id');
        $table->removeColumn('is_active');

        $table->addColumn('owner_foreign_key', 'char', [
            'default' => null,
            'null' => false,
            'limit' => 36 
        ]);

        $table->save();
    }
}
