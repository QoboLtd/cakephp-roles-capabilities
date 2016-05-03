<?php
namespace RolesCapabilities;

use ReflectionClass;
use ReflectionMethod;

trait CapabilityTrait
{
    /**
     * Returns permission capabilities.
     *
     * @param  string $controllerName Controller Name
     * @return array
     */
    public static function getCapabilities($controllerName = null)
    {
        $result = [];

        if (empty($controllerName)) {
            return $result;
        }

        $skipControllers = static::_getSkipControllers();
        if (in_array($controllerName, $skipControllers)) {
            return $result;
        }

        $skipActions = array_merge(static::_getSkipActions($controllerName), static::_getCakeControllerActions());

        $refClass = new ReflectionClass($controllerName);

        $actions = [];
        foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (!in_array($method->name, $skipActions)) {
                $actions[] = $method->name;
            }
        }

        $controllerName = str_replace('\\', '_', $controllerName);

        foreach ($actions as $action) {
            $result[] = new Capability(
                static::_generateCapabilityName($controllerName, $action), [
                    'label' => static::_generateCapabilityLabel($controllerName, $action),
                    'description' => static::_generateCapabilityDescription($controllerName, $action)
                ]
            );
        }

        return $result;
    }

    protected static function _getCakeControllerActions()
    {
        $result = get_class_methods('Cake\Controller\Controller');

        return $result;
    }

    protected static function _getSkipControllers()
    {
        $result = [
            'CakeDC\Users\Controller\SocialAccountsController',
            'App\Controller\PagesController'
        ];

        return $result;
    }

    protected static function _getSkipActions($controllerName)
    {
        $result = ['getCapabilities'];

        return $result;
    }

    protected static function _generateCapabilityName($controllerName, $action)
    {
        $result = 'cap__' . $controllerName . '__' . $action;

        return $result;
    }

    protected static function _generateCapabilityLabel($controllerName, $action)
    {
        $result = 'Cap ' . $controllerName . ' ' . $action;

        return $result;
    }

    protected static function _generateCapabilityDescription($controllerName, $action)
    {
        $result = 'Allow ' . $action;

        return $result;
    }
}
