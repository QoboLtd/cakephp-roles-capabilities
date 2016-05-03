<?php
namespace RolesCapabilities;

use Cake\Event\Event;
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

    /**
     * Check if current user has access to perform action.
     *
     * @param  Event  $event Event object
     * @return void
     * @throws Cake\Network\Exception\ForbiddenException
     * @todo                 this needs re-thinking
     */
    protected function _checkAccess(Event $event)
    {
        $requestParams = $event->subject()->request->params;
        $plugin = is_null($requestParams['plugin']) ? 'App' : $requestParams['plugin'];
        $controllerName = $plugin . '\\Controller\\' . $event->subject()->name . 'Controller';
        $capability = 'cap__' . $controllerName . '__' . $requestParams['action'];
        $allCapabilities = $this->getCapabilities($controllerName);
        $capExists = false;
        foreach ($allCapabilities as $cap) {
            if ($cap->getName() === $capability) {
                $capExists = true;
                break;
            }
        }

        $hasAccess = false;
        if ($capExists) {
            if ($this->Capability->hasAccess($capability)) {
                $hasAccess = true;
            } else {
                $hasAccess = false;
            }
        } else {
            /*
            if capability does not exist user is allowed access
             */
            $hasAccess = true;
        }

        /*
        superuser has access everywhere
         */
        if ($this->Auth->user('is_superuser')) {
            $hasAccess = true;
        }
        if (!$hasAccess) {
            throw new ForbiddenException();
        }
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
