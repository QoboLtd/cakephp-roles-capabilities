<?php

namespace RolesCapabilities\Access;

use Cake\Core\App;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use ReflectionClass;
use ReflectionMethod;

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
    public function getControllerTableInstance($controllerName)
    {
        $parts = explode('\\', $controllerName);
        // get last part, "/ArticlesController"
        $tableName = array_pop($parts);
        // remove "Controller" suffix from "/ArticlesController"
        $tableName = str_replace('Controller', '', $tableName);
        // remove "/Controller/" part
        array_pop($parts);
        // get plugin part "/MyPlugin/"
        $plugin = array_pop($parts);
        // prefix plugin to table name if is not "App"
        if ('App' !== $plugin) {
            $tableName = $plugin . '.' . $tableName;
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

}
