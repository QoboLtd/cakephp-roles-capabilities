<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class CapabilitiesUtil
{
    /**
     * @return string[]
     */
    public static function getTables(): array
    {
        $map = require 'vendor/composer/autoload_classmap.php';

        $tables = [];
        foreach ($map as $class => $path) {
            /* Skip non-tables */
            if (strpos($class, '\\Model\\Table\\') === false) {
                continue;
            }

            /* Skip non-tables: Part 2 */
            if (substr_compare($class, 'Table', -strlen('Table')) !== 0) {
                continue;
            }

            /* Skip test tables */
            if (strpos($class, '\\Test\\') !== false) {
                continue;
            }

            list($plugin, $table) = explode('\\Model\\Table\\', $class, 2);

            $table = basename($table, 'Table');

            if ($plugin === 'App') {
                $fullName = $table;
            } else {
                $plugin = str_replace('\\', '/', $plugin);
                $fullName = $plugin . '.' . $table;
            }

            $tables[] = $fullName;
        }

        return $tables;
    }

    /**
     * Gets the capabilities for the table.
     *
     * @return mixed[]
     */
    public static function getModelCapabilities(Table $table): array
    {
        $operations = Operation::values();
        $associations = [
            '' => 'Always',
        ];

        return [
            'operations' => $operations,
            'associations' => $associations,
        ];
    }

    public static function getAllCapabilities(): array
    {
        $locator = TableRegistry::getTableLocator();
        $resources = self::getTables();
        $skipTables = Configure::read('RolesCapabilities.skipTables', [
            'App',
        ]);

        $capabilities = [];
        foreach ($resources as $tableAlias) {
            if (in_array($tableAlias, $skipTables, true)) {
                continue;
            }
            $table = $locator->get($tableAlias);
            $capabilities[$table->getRegistryAlias()] = self::getModelCapabilities($table);
        }

        return $capabilities;
    }
}
