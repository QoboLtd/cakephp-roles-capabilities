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
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Log\Log;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Groups\Model\Table\GroupsTable;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;
use RolesCapabilities\Capability as Cap;
use RolesCapabilities\Model\Table\CapabilitiesTable;
use Webmozart\Assert\Assert;

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
     * Belongs to capability identifier
     */
    const CAP_TYPE_BELONGS = 'belongs';

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
     * @param mixed[] $url array of URL parameters.
     * @return string|null
     */
    public static function getControllerFullName(array $url): ?string
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
        if (empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Get full type capability identifier.
     *
     * @return string
     */
    public static function getTypeFull(): string
    {
        return static::CAP_TYPE_FULL;
    }

    /**
     * Get owner type capability identifier.
     *
     * @return string
     */
    public static function getTypeOwner(): string
    {
        return static::CAP_TYPE_OWNER;
    }

    /**
     * Get parent type capability identifier.
     *
     * @return string
     */
    public static function getTypeParent(): string
    {
        return static::CAP_TYPE_PARENT;
    }

    /**
     * Get belongsTo capability identifier
     *
     * @return string
     */
    public static function getTypeBelongs(): string
    {
        return static::CAP_TYPE_BELONGS;
    }

    /**
     * Method that retrieves and returns Controller public methods.
     *
     * @param  string $controllerName Controller name
     * @return mixed[]
     */
    public static function getControllerPublicMethods(string $controllerName): array
    {
        $actions = [];

        if (!class_exists($controllerName)) {
            return $actions;
        }

        $refClass = new \ReflectionClass($controllerName);
        foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $actions[] = $method->name;
        }

        return $actions;
    }

    /**
     * Get list of Cake's Controller class methods.
     *
     * @return mixed[]
     */
    public static function getCakeControllerActions(): array
    {
        $result = get_class_methods('Cake\Controller\Controller');

        return $result;
    }

    /**
     * Method that filter's out skipped actions from Controller's actions list.
     *
     * @param  string $controllerName Controller name
     * @param  mixed[]  $actions        Controller actions
     * @return mixed[]
     */
    public static function filterSkippedActions(string $controllerName, array $actions): array
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
     * @param  mixed[]  $actions        Action(s) to filter. If not specified all controller's public methods will be used.
     * @return mixed[]
     */
    public static function getActions(string $controllerName, array $actions = []): array
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
    public static function getControllerTableInstance(string $controllerName): \Cake\ORM\Table
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
    public static function generateCapabilityControllerName(string $controllerName): string
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
    public static function generateCapabilityName(string $controllerName, string $action): string
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
    public static function generateCapabilityLabel(string $controllerName, string $action): string
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
    public static function generateCapabilityDescription(string $controllerName, string $action): string
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
    public static function humanizeActionName(string $action): string
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
     * @param mixed[] $actions Controller actions
     * @return mixed[]
     */
    public static function getCapabilitiesForAction(Table $table, string $contrName, array $actions): array
    {
        $result = [];

        if (empty($contrName) || empty($actions)) {
            return $result;
        }

        $contrName = static::generateCapabilityControllerName($contrName);

        $fullCapabilities = static::generateFullCapabilities($contrName, $actions);
        if (!empty($fullCapabilities)) {
            $result[static::CAP_TYPE_FULL] = $fullCapabilities;
        }

        $ownerCapabilities = static::generateOwnerCapabilities($table, $contrName, $actions);
        if (!empty($ownerCapabilities)) {
            $result[static::CAP_TYPE_OWNER] = $ownerCapabilities;
        }

        $parentCapabilities = static::generateParentCapabilities($table, $contrName, $actions);
        if (!empty($parentCapabilities)) {
            $result[static::CAP_TYPE_PARENT] = $parentCapabilities;
        }

        $belongsToCaps = static::generateBelongsToCapabilities($table, $contrName, $actions);
        if (!empty($belongsToCaps)) {
            $result[static::CAP_TYPE_BELONGS] = $belongsToCaps;
        }

        return $result;
    }

    /**
     * Generate controller full capabilities.
     *
     * @param string $contrName Controller name
     * @param mixed[] $actions Controller actions
     * @return mixed[]
     */
    protected static function generateFullCapabilities(string $contrName, array $actions): array
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
     * @param mixed[] $actions Controller actions
     * @return mixed[]
     */
    protected static function generateOwnerCapabilities(Table $table, string $contrName, array $actions): array
    {
        $assignationFields = static::getTableAssignationFields($table);

        return static::generateCapabilities($contrName, $actions, $assignationFields);
    }

    /**
     * generateBelongsToCapabilities method to generate controller belongs to capabilities
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $contrName Controller name
     * @param mixed[] $actions Controller actions
     * @return mixed[]
     */
    protected static function generateBelongsToCapabilities(Table $table, string $contrName, array $actions): array
    {
        $assignationFields = static::getTableBelongsToFields($table);

        return static::generateCapabilities($contrName, $actions, $assignationFields);
    }

    /**
     * generateCapabilities method to generate controller belongs to capabilities
     *
     * @param string $contrName Controller name
     * @param mixed[] $actions Controller actions
     * @param mixed[] $assignationFields list of assignation fields
     * @param string $assignationType Type of association
     * @return mixed[]
     */
    protected static function generateCapabilities(string $contrName, array $actions, array $assignationFields, string $assignationType = ''): array
    {
        $result = [];

        if (empty($contrName) || empty($actions)) {
            return $result;
        }

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
                $suffix = ' if ' . (!empty($assignationType) ? $assignationType : 'owner') . ' (' . Inflector::humanize($assignationField) . ')';
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

                if (!empty($assignationType)) {
                    $result[$assignationType . '_(_' . $assignationField . '_)'][] = new Cap($name, $options);
                } else {
                    $result[] = new Cap($name, $options);
                }
            }
        }

        return $result;
    }

    /**
     * Generate controller parent capabilities.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $contrName Controller name
     * @param mixed[] $actions Controller actions
     * @return mixed[]
     */
    protected static function generateParentCapabilities(Table $table, string $contrName, array $actions): array
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
     * @return mixed[] capabilities
     */
    public static function getAllCapabilities(): array
    {
        $result = [];
        foreach (Utility::getControllers() as $controller) {
            if (!is_callable([$controller, 'getCapabilities'])) {
                continue;
            }

            $result[$controller] = $controller::getCapabilities($controller);
        }

        return $result;
    }

    /**
     *  fetchUserCapabilities() fetches user capabilities list
     *
     * @param string $userId    ID of user
     * @return mixed[]          list of user's capabilities or empty array
     */
    public static function fetchUserCapabilities(string $userId): array
    {
        $entities = [];

        if (array_key_exists($userId, static::$userCapabilities)) {
            return static::$userCapabilities[$userId];
        }

        $table = TableRegistry::get('RolesCapabilities.Capabilities');
        Assert::isInstanceOf($table, CapabilitiesTable::class);

        $userGroups = $table->getUserGroups($userId);
        if (empty($userGroups)) {
            return $entities;
        }

        $userRoles = $table->getGroupsRoles($userGroups);
        if (empty($userRoles)) {
            return $entities;
        }

        static::$userCapabilities[$userId] = $table->getUserRolesEntities($userRoles);

        return static::$userCapabilities[$userId];
    }

    /**
     * Returns Controller permission capabilities.
     *
     * @param  string $controllerName Controller name
     * @param  mixed[]  $actions        Controller actions
     * @return mixed[]
     */
    public static function getCapabilities(string $controllerName = null, array $actions = []): array
    {
        $result = [];

        if (empty($controllerName)) {
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
     * @param  mixed[]  $actionCapabilities Action capabilities
     * @param  mixed[]  $user               User info
     * @param  mixed[]  $url                Controller url
     * @return bool
     */
    public static function hasTypeAccess(string $type, array $actionCapabilities, array $user, array $url): bool
    {
        // skip if action has no access capabilities for specified type
        if (!isset($actionCapabilities[$type])) {
            return false;
        }

        $methodName = 'hasTypeAccess' . Inflector::camelize($type);

        if (! method_exists(get_called_class(), $methodName)) {
            Log::warning(sprintf(
                'Trying to check type access on non-existing method %s::%s',
                get_called_class(),
                $methodName
            ));

            return false;
        }

        $result = static::$methodName($actionCapabilities[$type], $user, $url);

        return $result;
    }

    /**
     * Method that checks if user has full access on Controller's action.
     *
     * @param  mixed[]  $capabilities Action capabilities
     * @param  mixed[]  $user               User info
     * @param  mixed[]  $url                Controller url
     * @return bool
     */
    protected static function hasTypeAccessFull(array $capabilities, array $user, array $url): bool
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
     * @param  mixed[]  $capabilities Action capabilities
     * @param  mixed[]  $user               User info
     * @param  mixed[]  $url                Controller url
     * @return bool
     */
    protected static function hasTypeAccessOwner(array $capabilities, array $user, array $url): bool
    {
        $entity = static::getEntityFromUrl($url);

        foreach ($capabilities as $capability) {
            if (!static::hasAccessInCapabilities($capability->getName(), $user['id'])) {
                continue;
            }

            // if url does not include an id and user has owner capability
            // access, to current module action, allow him access. (index action)
            if (empty($entity)) {
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
     * getUserGroups method
     *
     * @param mixed[] $user to get groups
     * @return mixed[] with group ID and name
     */
    protected static function getUserGroups(array $user): array
    {
        $table = TableRegistry::get('Groups.Groups');
        Assert::isInstanceOf($table, GroupsTable::class);

        return $table->getUserGroups($user['id'], [
            'fields' => ['id'],
            'contain' => [],
        ]);
    }

    /**
     * Method that checks if user has belongs to access on Controller's action.
     *
     * @param  mixed[]  $capabilities Action capabilities
     * @param  mixed[]  $user               User info
     * @param  mixed[]  $url                Controller url
     * @return bool
     */
    protected static function hasTypeAccessBelongs(array $capabilities, array $user, array $url): bool
    {
        $entity = static::getEntityFromUrl($url);

        $userGroups = static::getUserGroups($user);

        foreach ($capabilities as $capability) {
            if (!static::hasAccessInCapabilities($capability->getName(), $user['id'])) {
                continue;
            }

            // if url does not include an id and user has belongs to capability
            // access, to current module action, allow him access. (index action)
            if (empty($entity)) {
                return true;
            }

            // if url includes an id, check capability's field value from the entity
            // against current user id and if they match allow him access. (view, edit actions etc)
            $field = $capability->getField();

            foreach (array_keys($userGroups) as $id) {
                if ($entity->get($field) === $id) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * getEntityFromUrl method gets entity ID from given URL if so
     *
     * @param mixed[] $url to get ID
     * @return \Cake\Datasource\EntityInterface|null
     */
    protected static function getEntityFromUrl(array $url) : ?EntityInterface
    {
        $entity = null;

        $id = null;
        if (!empty($url['pass'][0])) {
            $id = $url['pass'][0];
        }

        if (empty($id) && !empty($url['0'])) {
            $id = $url['0'];
        }

        // if url includes an id, fetch relevant record
        if (!empty($id)) {
            try {
                $tableName = $url['controller'];
                if (!empty($url['plugin'])) {
                    $tableName = $url['plugin'] . '.' . $tableName;
                }
                $table = TableRegistry::get($tableName);
                $entity = $table->get($id);
            } catch (InvalidPrimaryKeyException $e) {
                Log::warning(sprintf('Failed to fetch record with id [%s] from table [%s]', $id, $url['controller']));
            } catch (RecordNotFoundException $e) {
                Log::warning(sprintf('Failed to fetch record with id [%s] from table [%s]', $id, $url['controller']));
            }
        }

        return $entity;
    }

    /**
     * Method that checks if current user is allowed access.
     *
     * Returns true if current user has access, false otherwise.
     *
     * @param  string $capability capability name
     * @param  string $userId     user id
     * @return bool
     */
    public static function hasAccessInCapabilities(string $capability, string $userId): bool
    {
        $userCaps = static::fetchUserCapabilities($userId);
        if (in_array($capability, $userCaps)) {
            return true;
        }

        return false;
    }

    /**
     * Method that retrieves and returns Table's assignation fields.
     *
     * These are fields that dictate assigment, usually foreign key
     * associated with Users tables. (example: assigned_to)
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    public static function getTableAssignationFields(Table $table): array
    {
        $fields = [];
        $assignationModels = Configure::read('RolesCapabilities.accessCheck.assignationModels');

        foreach ($table->associations() as $association) {
            // skip non-assignation models
            if (!in_array($association->className(), $assignationModels)) {
                continue;
            }

            $fields[] = $association->getForeignKey();
        }

        return $fields;
    }

    /**
     * Method that retrieves and returns Table's belongs to fields.
     *
     * These are fields that dictate assigment, usually foreign key
     * associated with Groups tables. (example: belongs_to)
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    public static function getTableBelongsToFields(Table $table): array
    {
        $fields = [];
        $belongsToModels = Configure::read('RolesCapabilities.accessCheck.belongsToModels');

        foreach ($table->associations() as $association) {
            // skip non-assignation models
            if (!in_array($association->className(), $belongsToModels)) {
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
     * @return mixed[]
     */
    public static function getTableParentModules(Table $table): array
    {
        $result = [];

        list(, $moduleName) = pluginSplit($table->getRegistryAlias());

        try {
            $config = new ModuleConfig(ConfigType::MODULE(), $moduleName);
            $moduleConfig = $config->parseToArray();
            $result = Hash::get($moduleConfig, 'table.permissions_parent_modules', []);
        } catch (\InvalidArgumentException $e) {
            return $result;
        }

        return $result;
    }

    /**
     * normalizeControllerName method
     *
     * @param mixed[] $url including plugin if so, controller and action
     * @return string full controller name including App or Plugin
     */
    public static function normalizeControllerName(array $url): string
    {
        $plugin = !empty($url['plugin']) ? $url['plugin'] : 'App';
        /**
         * @var string $plugin
         */
        $plugin = preg_replace('/\//', '\\', $plugin);
        if (empty($plugin)) {
            $plugin = '';
        }
        $controllerName = $plugin . '\\Controller\\' . $url['controller'] . 'Controller';

        return $controllerName;
    }

    /**
     * getReportToUsers method
     *
     * @param string $userId to find reported to users
     * @return mixed[]
     */
    public static function getReportToUsers(string $userId): array
    {
        $table = TableRegistry::get(Configure::read('Users.table'));
        $users = $table->find()
            ->where([
                'reports_to' => $userId
            ])
            ->all()
            ->toArray();

        return $users;
    }
}
