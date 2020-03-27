<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace RolesCapabilities\Capability;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;
use RolesCapabilities\Access\ResourceInterface;

/**
 * Capabilities generator class.
 */
final class Generator
{
    /**
     * Generates model resource-based capabilities.
     *
     * @param string $model Model name
     * @return \RolesCapabilities\Capability\CapabilityInterface[]
     */
    public function resources(string $model): array
    {
        $className = App::className($model, 'Model/Table', 'Table');
        if (false === $className) {
            return [];
        }

        $resource = str_replace(['\\', '.'], '_', $className);

        return [
            ResourceInterface::OPERATION_CREATE => $this->getCreateCapabilities($resource),
            ResourceInterface::OPERATION_READ => $this->getReadCapabilities($resource, $model),
            ResourceInterface::OPERATION_UPDATE => $this->getUpdateCapabilities($resource, $model),
            ResourceInterface::OPERATION_DELETE => $this->getDeleteCapabilities($resource, $model)
        ];
    }

    /**
     * Generates controller actions-based capabilities.
     *
     * @param string $model Model name
     * @return \RolesCapabilities\Capability\CapabilityInterface[]
     */
    public function actions(string $model): array
    {
        $className = App::className($model, 'Controller', 'Controller');
        if (! class_exists($className)) {
            return [];
        }

        if (in_array($className, Configure::read('RolesCapabilities.accessCheck.skipControllers'))) {
            return [];
        }

        $publicMethods = self::getPublicMethods($className);
        $skippedMethods = self::getSkippedMethods($className);

        // filter out skipped methods
        $methods = array_filter($publicMethods, function ($publicMethod) use ($skippedMethods) {
            return ! in_array($publicMethod, $skippedMethods);
        });

        if (empty($methods)) {
            return [];
        }

        $controller = str_replace('\\', '_', $className);

        $result = [];
        foreach ($methods as $method) {
            $result[] = new ActionCapability($controller, $method);
        }

        return $result;
    }

    /**
     * Generates all capabilities allowed to the provided user.
     *
     * @param mixed[] $user User info
     * @return \RolesCapabilities\Capability\CapabilityInterface[]
     */
    public function user(array $user): array
    {
        throw new \LogicException('To be implemented');
    }

    private function getCreateCapabilities(string $resource) : array
    {
        return [new FullCapability($resource, ResourceInterface::OPERATION_CREATE)];
    }

    private function getReadCapabilities(string $resource, string $model) : array
    {
        $result = [new FullCapability($resource, ResourceInterface::OPERATION_READ)];

        foreach (self::getAssignationFields($model) as $field) {
            $result[] = new OwnerCapability($resource, ResourceInterface::OPERATION_READ, $field);
        }

        foreach (self::getBelongsFields($model) as $field) {
            $result[] = new BelongsCapability($resource, ResourceInterface::OPERATION_READ, $field);
        }

        $parentModules = self::getParentModules($model);
        if ([] !== $parentModules) {
            $result[] = new ParentCapability($resource, ResourceInterface::OPERATION_READ, $parentModules);
        }

        return $result;
    }

    private function getUpdateCapabilities(string $resource, string $model) : array
    {
        $result = [new FullCapability($resource, ResourceInterface::OPERATION_UPDATE)];

        foreach (self::getAssignationFields($model) as $field) {
            $result[] = new OwnerCapability($resource, ResourceInterface::OPERATION_UPDATE, $field);
        }

        foreach (self::getBelongsFields($model) as $field) {
            $result[] = new BelongsCapability($resource, ResourceInterface::OPERATION_UPDATE, $field);
        }

        return $result;
    }

    private function getDeleteCapabilities(string $resource, string $model) : array
    {
        $result = [new FullCapability($resource, ResourceInterface::OPERATION_DELETE)];

        foreach (self::getAssignationFields($model) as $field) {
            $result[] = new OwnerCapability($resource, ResourceInterface::OPERATION_DELETE, $field);
        }

        foreach (self::getBelongsFields($model) as $field) {
            $result[] = new BelongsCapability($resource, ResourceInterface::OPERATION_DELETE, $field);
        }

        return $result;
    }

    /**
     * Method that retrieves and returns Table's assignation fields.
     *
     * These are fields that dictate assigment, usually foreign key
     * associated with Users tables. (example: assigned_to)
     *
     * @param string $model Model name
     * @return string[]
     */
    private static function getAssignationFields(string $model): array
    {
        $assignationModels = Configure::read('RolesCapabilities.accessCheck.assignationModels');

        $result = [];
        foreach (TableRegistry::getTableLocator()->get($model)->associations() as $association) {
            // skip non-assignation models
            if (in_array($association->className(), $assignationModels)) {
                $result[] = $association->getForeignKey();
            }
        }

        return $result;
    }

    /**
     * Method that retrieves and returns Table's belongs to fields.
     *
     * These are fields that dictate assigment, usually foreign key
     * associated with Groups tables. (example: belongs_to)
     *
     * @param string $model Model name
     * @return string[]
     */
    private static function getBelongsFields(string $model): array
    {
        $belongsToModels = Configure::read('RolesCapabilities.accessCheck.belongsToModels');

        $result = [];
        foreach (TableRegistry::getTableLocator()->get($model)->associations() as $association) {
            // skip non-assignation models
            if (in_array($association->className(), $belongsToModels)) {
                $result[] = $association->foreignKey();
            }
        }

        return $result;
    }

    /**
     * Get table parent modules from module configuration.
     *
     * @param string $model Model name
     * @return mixed[]
     */
    private static function getParentModules(string $model): array
    {
        list(, $modelName) = pluginSplit(TableRegistry::getTableLocator()->get($model)->getRegistryAlias());

        try {
            $config = (new ModuleConfig(ConfigType::MODULE(), $modelName))->parseToArray();

            return Hash::get($config, 'table.permissions_parent_modules', []);
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }

    /**
     * Returns provided class public methods.
     *
     * @param string $className Class name
     * @return string[]
     */
    private static function getPublicMethods(string $className): array
    {
        $result = [];
        foreach ((new \ReflectionClass($className))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $result[] = $method->name;
        }

        return $result;
    }

    /**
     * Controller skipped-methods getter.
     *
     * @param string $controller Controller name
     * @return string[]
     */
    private static function getSkippedMethods(string $controller): array
    {
        return array_merge(
            // skip actions for all controllers, if defined in the plugin's configuration.
            Configure::read('RolesCapabilities.accessCheck.skipActions.*', []),
            // skip actions for specified controller, if defined in the plugin's configuration.
            Configure::read('RolesCapabilities.accessCheck.skipActions.' . $controller, []),
            // skip cake's controller class methods.
            get_class_methods('Cake\Controller\Controller')
        );
    }
}
