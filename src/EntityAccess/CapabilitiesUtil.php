<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Qobo\Utils\Module\Exception\MissingModuleException;
use RolesCapabilities\Model\Behavior\AuthorizedBehavior;
use Webmozart\Assert\Assert;

class CapabilitiesUtil
{
    /**
     * Returns a list of all tables.
     *
     * It works by scanning composer's autoload classmap
     *
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
     * Gets all capabilities defined in the model
     *
     * @param \Cake\ORM\Table $table The table to get the capabilities
     *
     * @return mixed[]
     */
    public static function getModelStaticCapabities(Table $table): array
    {
        if (!$table->hasBehavior('Authorized')) {
            return [];
        }

        $behavior = $table->getBehavior('Authorized');
        Assert::isInstanceOf($behavior, AuthorizedBehavior::class);

        return $behavior->getCapabilities();
    }

    /**
     * Gets all capabilities defined in the model
     *
     * @param \Cake\ORM\Table $table The table to get the associations
     *
     * @return mixed[]
     */
    public static function getModelCapabilityAssociations(Table $table): array
    {
        if (!$table->hasBehavior('Authorized')) {
            return [];
        }

        $behavior = $table->getBehavior('Authorized');
        Assert::isInstanceOf($behavior, AuthorizedBehavior::class);

        return $behavior->getAssociations();
    }

    /**
     * Gets all capability metadata from all tables
     * For each table it contains the associations and
     * operations involved in capabilities.
     *
     * @return mixed[]
     */
    public static function getAllCapabilities(): array
    {
        $locator = TableRegistry::getTableLocator();
        $resources = self::getTables();

        $capabilities = [];
        foreach ($resources as $tableAlias) {
            try {
                $table = $locator->get($tableAlias);
            } catch (MissingModuleException $e) {
                continue;
            }

            if (!$table->hasBehavior('Authorized')) {
                continue;
            }
            $behavior = $table->getBehavior('Authorized');
            Assert::isInstanceOf($behavior, AuthorizedBehavior::class);

            $capabilities[$table->getRegistryAlias()] = [
                'associations' => $behavior->getAssociations(),
                'operations' => $behavior->getOperations(),
                'capabilities' => $behavior->getCapabilities(),
            ];
        }

        return $capabilities;
    }
}
