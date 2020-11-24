<?php

use Cake\Utility\Inflector;
use Migrations\AbstractMigration;

class MigrateCapabilities extends AbstractMigration
{

    private function convertCapability(string $cap): array
    {
        if (substr($cap, 0, 5) !== 'cap__') {
            die('Unsupported cap');
        }

        $cap = substr($cap, 5);

        list($controller, $operation) = explode('__', $cap);
        list($plugin, $controller) = explode('_Controller_', $controller); 

        $plugin = str_replace('_', '/', $plugin);

        if ($plugin === 'App') {
            $resource = basename($controller, 'Controller');
        } else {
            $resource = $plugin . '.' . basename($controller, 'Controller');
        }

        $opParts = explode('_', $operation, 2);

        if (count($opParts) === 2) {
            $operation = $opParts[0];
            $association = Inflector::camelize($opParts[1]);
        } else {
            $association = 'All';
        }

        return [
            'resource' => $resource,
            'operation' => $operation,
            'association' => $association,
            'field' => $field,
        ];
    }

    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        $builder = $this->getQueryBuilder();

        $capabilities = $builder
                            ->select(['id', 'role_id', 'name'])
                            ->distinct(['id', 'role_id', 'name'])
                            ->from('qobo_capabilities')->execute()->fetchAll();

        $newcaps = [];
        foreach ($capabilities as $capability) {
            $newcap = array_merge(
                [
                    'id' => $capability[0],
                    'role_id' => $capability[1],
                ],
                $this->convertCapability($capability[2])
            );

            $newcaps[] = $newcap;
        }

        $this->table('qobo_extended_capabilities')->insert($newcaps)->save();

        $this->table('qobo_capabilities')->drop()->save();
    }
}
