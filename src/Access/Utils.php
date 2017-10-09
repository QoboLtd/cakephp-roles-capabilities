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
namespace RolesCapabilities\Access;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use DirectoryIterator;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use ReflectionClass;
use ReflectionMethod;
use RolesCapabilities\Capability as Cap;

/**
 *  Utils class with common methos for Capabilities
 *
 *
 */
class Utils
{
    /**
     * Full type capability identifier
     */
    const CAP_TYPE_FULL = 'full';

    /**
     * Owner type capability identifier
     */
    const CAP_TYPE_OWNER = 'owner';

    /**
     * Parent type capability identifier
     */
    const CAP_TYPE_PARENT = 'parent';

    /**
     * Non-assigned actions
     *
     * @var array
     */
    protected static $nonAssignedActions = [
        'add'
    ];

    protected static $capabilitiesTable = null;

    /**
     * Cached user capabilities by user id.
     *
     * @var array
     */
    protected static $userCapabilities = [];

    /**
     * Returns Controller's class name namespaced.
     *
     * @param array $url array of URL parameters.
     * @return string
     */
    public static function getControllerFullName(array $url)
    {
        $result = null;

        if (empty($url['controller'])) {
            return $result;
        }

        $class = $url['controller'];
        if (!empty($url['plugin'])) {
            $class = $url['plugin'] . '.' . $class;
        }
        $result = App::className($class . 'Controller', 'Controller');

        return $result;
    }

    /**
     * Get full type capability identifier.
     *
     * @return string
     */
    public static function getTypeFull()
    {
        return static::CAP_TYPE_FULL;
    }

    /**
     * Get owner type capability identifier.
     *
     * @return string
     */
    public static function getTypeOwner()
    {
        return static::CAP_TYPE_OWNER;
    }

    /**
     * Get parent type capability identifier.
     *
     * @return string
     */
    public static function getTypeParent()
    {
        return static::CAP_TYPE_PARENT;
    }

    /**
     * Method that retrieves and returns Controller public methods.
     *
     * @param  string $controllerName Controller name
     * @return array
     */
    public static function getControllerPublicMethods($controllerName)
    {
        $actions = [];
        $refClass = new ReflectionClass($controllerName);
        foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $actions[] = $method->name;
        }

        return $actions;
    }

    /**
     * Get list of Cake's Controller class methods.
     *
     * @return array
     */
    public static function getCakeControllerActions()
    {
        $result = get_class_methods('Cake\Controller\Controller');

        return $result;
    }

    /**
     * Method that filter's out skipped actions from Controller's actions list.
     *
     * @param  string $controllerName Controller name
     * @param  array  $actions        Controller actions
     * @return array
     */
    public static function filterSkippedActions($controllerName, array $actions)
    {
        $skipActions = [];
        if (is_callable([$controllerName, 'getSkipActions'])) {
            $skipActions = $controllerName::getSkipActions($controllerName);
        }

        // skip actions for all controllers, if defined in the plugin's configuration.
        $allSkipActions = Configure::read('RolesCapabilities.accessCheck.skipActions.*');
        if (!empty($allSkipActions)) {
            $skipActions = array_merge($skipActions, $allSkipActions);
        }

        // skip actions for specified controller, if defined in the plugin's configuration.
        $controllerSkipActions = Configure::read('RolesCapabilities.accessCheck.skipActions.' . $controllerName);
        if (!empty($controllerSkipActions)) {
            $skipActions = array_merge($skipActions, $controllerSkipActions);
        }

        $skipActions = array_merge(
            $skipActions,
            static::getCakeControllerActions()
        );

        foreach ($actions as $k => $action) {
            if (in_array($action, $skipActions)) {
                unset($actions[$k]);
            }
        }

        return $actions;
    }

    /**
     * Method that filters and returns Controller action(s) that can be used for generating access capabilities.
     *
     * @param  string $controllerName Controller name
     * @param  array  $actions        Action(s) to filter. If not specified all controller's public methods will be used.
     * @return array
     */
    public static function getActions($controllerName, array $actions = [])
    {
        $publicMethods = static::getControllerPublicMethods($controllerName);
        // return if controller has no public methods
        if (empty($publicMethods)) {
            return [];
        }

        // if no actions defined, use controller's public methods
        if (!empty($actions)) {
            $actions = array_intersect($actions, $publicMethods);
        } else { // else use controller's public methods
            $actions = $publicMethods;
        }

        if (empty($actions)) {
            return $actions;
        }

        // filter out skipped actions
        $actions = static::filterSkippedActions($controllerName, $actions);

        return $actions;
    }

    /**
     * Method that returns Table instance of specified controller.
     *
     * @param  string          $controllerName Controller name
     * @return \Cake\ORM\Table
     */
    public static function getControllerTableInstance($controllerName)
    {
        $tableName = App::shortName($controllerName, 'Controller', 'Controller');

        // remove vendor prefix
        if (false !== strpos($tableName, '/')) {
            $tableName = substr($tableName, strpos($tableName, '/') + 1);
        }

        return TableRegistry::get($tableName);
    }

    /**
     * Generate capability's controller name.
     *
     * @param  string $controllerName Controller name
     * @return string
     */
    public static function generateCapabilityControllerName($controllerName)
    {
        $result = str_replace('\\', '_', $controllerName);

        return $result;
    }

    /**
     * Generate capability name.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    public static function generateCapabilityName($controllerName, $action)
    {
        $result = 'cap__' . $controllerName . '__' . $action;

        return $result;
    }

    /**
     * Generate capability label.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    public static function generateCapabilityLabel($controllerName, $action)
    {
        $result = 'Cap ' . $controllerName . ' ' . $action;

        return $result;
    }

    /**
     * Generate capability description.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    public static function generateCapabilityDescription($controllerName, $action)
    {
        $result = 'Allow ' . $action;

        return $result;
    }

    /**
     * Convert action/method name to human-friendly description
     *
     * Action/method names mostly follow CakePHP naming conventions
     * and are not very human-friendly.  For example, 'list' is much
     * less confusing than 'index'.
     *
     * When used in the capability description, an additional layer of
     * confusion is introduced.  For example, 'Allow info' or 'Allow
     * changelog'.  Adjusting these to 'Allow view info' and 'Allo
     * view changelog' help a great deal.
     *
     * @todo Allow controllers to take control over these
     *
     * @param string $action Action/method name to humanize
     * @return string
     */
    public static function humanizeActionName($action)
    {
        // cameCaseMethod -> under_score -> Human Form -> lowercase
        $result = strtolower(Inflector::humanize(Inflector::underscore($action)));

        switch ($action) {
            case 'index':
                $result = 'list';
                break;
            case 'info':
            case 'changelog':
                $result = 'view ' . $action;
                break;
        }

        return $result;
    }

    /**
     * Method that generates capabilities for specified controller's actions.
     * Capabilities included are full or owner access types.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $contrName Controller name
     * @param array $actions Controller actions
     * @return array
     */
    public static function getCapabilitiesForAction(Table $table, $contrName, array $actions)
    {
        $result = [];

        if (empty($contrName) || empty($actions)) {
            return $result;
        }

        $contrName = static::generateCapabilityControllerName($contrName);

        $fullCapabilities = static::_generateFullCapabilities($contrName, $actions);
        if (!empty($fullCapabilities)) {
            $result[static::CAP_TYPE_FULL] = $fullCapabilities;
        }

        $ownerCapabilities = static::_generateOwnerCapabilities($table, $contrName, $actions);
        if (!empty($ownerCapabilities)) {
            $result[static::CAP_TYPE_OWNER] = $ownerCapabilities;
        }

        $parentCapabilities = static::_generateParentCapabilities($table, $contrName, $actions);
        if (!empty($parentCapabilities)) {
            $result[static::CAP_TYPE_PARENT] = $parentCapabilities;
        }

        return $result;
    }

    /**
     * Generate controller full capabilities.
     *
     * @param string $contrName Controller name
     * @param array $actions Controller actions
     * @return array
     */
    protected static function _generateFullCapabilities($contrName, array $actions)
    {
        $result = [];

        if (empty($contrName) || empty($actions)) {
            return $result;
        }

        // generate action's full (all) type capabilities
        foreach ($actions as $action) {
            $name = static::generateCapabilityName($contrName, $action);
            $options = [
                'label' => static::generateCapabilityLabel($contrName, $action . '_all'),
                'description' => static::generateCapabilityDescription($contrName, static::humanizeActionName($action))
            ];

            $result[] = new Cap($name, $options);
        }

        return $result;
    }

    /**
     * Generate controller owner capabilities.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $contrName Controller name
     * @param array $actions Controller actions
     * @return array
     */
    protected static function _generateOwnerCapabilities(Table $table, $contrName, array $actions)
    {
        $result = [];

        if (empty($contrName) || empty($actions)) {
            return $result;
        }

        $assignationFields = static::getTableAssignationFields($table);
        if (empty($assignationFields)) {
            return $result;
        }

        foreach ($actions as $action) {
            // skip rest of the logic if assignment fields are not found
            // or if current action does not support assignment (Example: add / create)
            if (in_array($action, static::$nonAssignedActions)) {
                continue;
            }

            // generate action's owner (assignment field) type capabilities
            foreach ($assignationFields as $assignationField) {
                $label = static::generateCapabilityLabel($contrName, $action . '_' . $assignationField);
                $suffix = ' if owner (' . Inflector::humanize($assignationField) . ')';
                $description = static::generateCapabilityDescription(
                    $contrName,
                    static::humanizeActionName($action) . $suffix
                );
                $field = $assignationField;

                $name = static::generateCapabilityName($contrName, $action . '_' . $assignationField);
                $options = [
                    'label' => $label,
                    'description' => $description,
                    'field' => $field
                ];

                $result[] = new Cap($name, $options);
            }
        }

        return $result;
    }

    /**
     * Generate controller parent capabilities.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $contrName Controller name
     * @param array $actions Controller actions
     * @return array
     */
    protected static function _generateParentCapabilities(Table $table, $contrName, array $actions)
    {
        $result = [];

        if (empty($contrName) || empty($actions)) {
            return $result;
        }

        $parentModules = static::getTableParentModules($table);
        if (empty($parentModules)) {
            return $result;
        }

        $name = static::generateCapabilityName($contrName, 'fetch_parent');
        $options = [
            'label' => static::generateCapabilityLabel($contrName, 'fetch_parent'),
            'description' => static::generateCapabilityDescription(
                $contrName,
                'fetch if owner on parent module (' . implode(', ', $parentModules) . ')'
            ),
            'parent_modules' => $parentModules
        ];

        $result[] = new Cap($name, $options);

        return $result;
    }

    /**
     * Method that retrieves all capabilities.
     *
     * @return array capabilities
     */
    public static function getAllCapabilities()
    {
        $result = [];
        foreach (self::getControllers() as $controller) {
            if (!is_callable([$controller, 'getCapabilities'])) {
                continue;
            }

            $result[$controller] = $controller::getCapabilities($controller);
        }

        return $result;
    }

    /**
     * Method that returns all controller names.
     * @param  bool  $includePlugins flag for including plugin controllers
     * @return array                 controller names
     */
    public static function getControllers($includePlugins = true)
    {
        $controllers = self::getDirControllers(APP . 'Controller' . DS);

        if (true === $includePlugins) {
            $plugins = Plugin::loaded();
            foreach ($plugins as $plugin) {
                // plugin path
                $path = Plugin::path($plugin) . 'src' . DS . 'Controller' . DS;
                $controllers = array_merge($controllers, self::getDirControllers($path, $plugin));
            }
        }

        return $controllers;
    }

    /**
     * Method that retrieves controller names
     * found on the provided directory path.
     * @param  string $path   directory path
     * @param  string $plugin plugin name
     * @param  bool   $fqcn   flag for using fqcn
     * @return array          controller names
     */
    public static function getDirControllers($path, $plugin = null, $fqcn = true)
    {
        $controllers = [];
        if (file_exists($path)) {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $fileinfo) {
                $className = $fileinfo->getBasename('.php');
                if ($fileinfo->isFile() && 'AppController' !== $className) {
                    if (!empty($plugin)) {
                        $className = $plugin . '.' . $className;
                    }

                    if (true === $fqcn) {
                        $className = App::className($className, 'Controller');
                    }

                    $controllers[] = $className;
                }
            }
        }

        return $controllers;
    }

    /**
     *  fetchUserCapabilities() fetches user capabilities list
     *
     * @param string $userId    ID of user
     * @return array            list of user's capabilities or empty array
     */
    public static function fetchUserCapabilities($userId)
    {
        $entities = [];

        if (array_key_exists($userId, static::$userCapabilities)) {
            return static::$userCapabilities[$userId];
        }

        $userGroups = static::_getCapabilitiesTable()->getUserGroups($userId);
        if (empty($userGroups)) {
            return $entities;
        }

        $userRoles = static::_getCapabilitiesTable()->getGroupsRoles($userGroups);
        if (empty($userRoles)) {
            return $entities;
        }

        static::$userCapabilities[$userId] = static::_getCapabilitiesTable()->getUserRolesEntities($userRoles);

        return static::$userCapabilities[$userId];
    }

    /**
     * Returns Controller permission capabilities.
     *
     * @param  string $controllerName Controller name
     * @param  array  $actions        Controller actions
     * @return array
     */
    public static function getCapabilities($controllerName = null, array $actions = [])
    {
        $result = [];

        if (is_null($controllerName) || !is_string($controllerName)) {
            return $result;
        }

        $skipControllers = Configure::read('RolesCapabilities.accessCheck.skipControllers');
        if (is_callable([$controllerName, 'getSkipControllers'])) {
            $skipControllers = array_merge($skipControllers, $controllerName::getSkipControllers());
        }

        if (in_array($controllerName, $skipControllers)) {
            return $result;
        }

        $actions = static::getActions($controllerName, $actions);

        if (empty($actions)) {
            return $result;
        }

        // get controller table instance
        $table = static::getControllerTableInstance($controllerName);

        $result = static::getCapabilitiesForAction($table, $controllerName, $actions);

        return $result;
    }

    /**
     * Method that checks if user has full access on Controller's action.
     *
     * @param  string $type               Capability type
     * @param  array  $actionCapabilities Action capabilities
     * @param  array  $user               User info
     * @param  array  $url                Controller url
     * @return bool
     */
    public static function hasTypeAccess($type, array $actionCapabilities, array $user, array $url)
    {
        // skip if action has no access capabilities for specified type
        if (!isset($actionCapabilities[$type])) {
            return false;
        }

        // @todo check if method exists
        $methodName = 'hasTypeAccess' . ucfirst($type);
        $result = static::$methodName($actionCapabilities[$type], $user, $url);

        return $result;
    }

    /**
     * Method that checks if user has full access on Controller's action.
     *
     * @param  array  $capabilities Action capabilities
     * @param  array  $user               User info
     * @param  array  $url                Controller url
     * @return bool
     */
    protected static function hasTypeAccessFull(array $capabilities, array $user, array $url)
    {
        foreach ($capabilities as $capability) {
            if (static::hasAccessInCapabilities($capability->getName(), $user['id'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Method that checks if user has full access on Controller's action.
     *
     * @param  array  $capabilities Action capabilities
     * @param  array  $user               User info
     * @param  array  $url                Controller url
     * @return bool
     */
    protected static function hasTypeAccessOwner(array $capabilities, array $user, array $url)
    {
        $id = null;
        if (!empty($url['pass'][0])) {
            $id = $url['pass'][0];
        }

        if (empty($id) && !empty($url['0'])) {
            $id = $url['0'];
        }

        // if url includes an id, fetch relevant record
        if (!empty($id)) {
            $entity = null;
            try {
                $tableName = $url['controller'];
                if (!empty($url['plugin'])) {
                    $tableName = $url['plugin'] . '.' . $tableName;
                }
                $table = TableRegistry::get($tableName);
                $entity = $table->get($id);
            } catch (Exception $e) {
                return false;
            }
        }

        foreach ($capabilities as $capability) {
            if (!static::hasAccessInCapabilities($capability->getName(), $user['id'])) {
                continue;
            }

            // if url does not include an id and user has owner capability
            // access, to current module action, allow him access. (index action)
            if (empty($id)) {
                return true;
            }

            // if url includes an id, check capability's field value from the entity
            // against current user id and if they match allow him access. (view, edit actions etc)
            $field = $capability->getField();
            if ($entity->get($field) === $user['id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get instance of Capabilities Table.
     *
     * @return object Capabilities Table object
     */
    protected static function _getCapabilitiesTable()
    {
        if (empty(static::$capabilitiesTable)) {
            static::$capabilitiesTable = TableRegistry::get('RolesCapabilities.Capabilities');
        }

        return static::$capabilitiesTable;
    }

    /**
     * Method that checks if current user is allowed access.
     * Returns true if current user has access, false otherwise.
     * @param  string $capability capability name
     * @param  string $userId     user id
     * @return bool
     */
    public static function hasAccessInCapabilities($capability, $userId)
    {
        $userCaps = static::fetchUserCapabilities($userId);
        if (in_array($capability, $userCaps)) {
            return true;
        }

        return false;
    }

    /**
     * Method that retrieves and returns Table's assignation fields. These are fields
     * that dictate assigment, usually foreign key associated with a Users tables. (example: assigned_to)
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return array
     */
    public static function getTableAssignationFields(Table $table)
    {
        $fields = [];
        $assignationModels = Configure::read('RolesCapabilities.accessCheck.assignationModels');
        foreach ($table->associations() as $association) {
            // skip non-assignation models
            if (!in_array($association->className(), $assignationModels)) {
                continue;
            }

            $fields[] = $association->foreignKey();
        }

        return $fields;
    }

    /**
     * Get table parent modules from module configuration.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    public static function getTableParentModules(Table $table)
    {
        list(, $moduleName) = pluginSplit($table->registryAlias());

        $config = new ModuleConfig(ConfigType::MODULE(), $moduleName);
        $moduleConfig = $config->parse();

        return $moduleConfig->table->permissions_parent_modules;
    }

    /**
     * normalizeControllerName method
     *
     * @param array $url including plugin if so, controller and action
     * @return string full controller name including App or Plugin
     */
    public static function normalizeControllerName(array $url)
    {
        $plugin = !empty($url['plugin']) ? $url['plugin'] : 'App';
        $plugin = preg_replace('/\//', '\\', $plugin);
        $controllerName = $plugin . '\\Controller\\' . $url['controller'] . 'Controller';

        return $controllerName;
    }
}
