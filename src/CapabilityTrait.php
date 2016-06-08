<?php
namespace RolesCapabilities;

use Cake\Core\App;
use Cake\Event\Event;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;

trait CapabilityTrait
{
    /**
     * Capabilities Table instance.
     *
     * @var object
     */
    protected static $_capabilitiesTable;

    /**
     * Get instance of Capabilities Table.
     *
     * @return object Capabilities Table object
     */
    protected static function _getCapabilitiesTable()
    {
        if (empty(static::$_capabilitiesTable)) {
            static::$_capabilitiesTable = TableRegistry::get('RolesCapabilities.Capabilities');
        }

        return static::$_capabilitiesTable;
    }

    /**
     * Returns permission capabilities.
     *
     * @param  string $controllerName Controller Name
     * @return array
     */
    public static function getCapabilities($controllerName = null)
    {
        return static::_getCapabilitiesTable()->getCapabilities($controllerName);
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
        $controllerName = App::className($plugin . '.' . $event->subject()->name . 'Controller', 'Controller');
        $capability = static::_generateCapabilityName(
            static::_generateCapabilityControllerName($controllerName),
            $requestParams['action']
        );
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

    /**
     * Check if specified role has access to perform action.
     *
     * @param  string  $role role uuid
     * @return bool
     * @throws Cake\Network\Exception\ForbiddenException
     */
    protected function _checkRoleAccess($role, $handle = true)
    {
        $hasAccess = false;

        if ($this->Capability->hasRoleAccess($role)) {
            $hasAccess = true;
        }

        /*
        superuser has access everywhere
         */
        if ($this->Auth->user('is_superuser')) {
            $hasAccess = true;
        }

        if (!$handle) {
            return $hasAccess;
        }

        if (!$hasAccess) {
            throw new ForbiddenException();
        }
    }

    /**
     * Get list of Cake's Controller class methods.
     *
     * @return array
     */
    protected static function _getCakeControllerActions()
    {
        return static::_getCapabilitiesTable()->getCakeControllerActions();
    }

    /**
     * Get list of skipped controllers.
     *
     * @return array
     */
    public static function getSkipControllers()
    {
        return static::_getCapabilitiesTable()->getSkipControllers();
    }

    /**
     * Get list of skipped controllers.
     *
     * @deprecated
     * @return array
     */
    protected static function _getSkipControllers()
    {
        return static::getSkipControllers();
    }

    /**
     * Get list of controller's skipped actions.
     *
     * @param  string $controllerName Controller name
     * @return array
     */
    public static function getSkipActions($controllerName)
    {
        return static::_getCapabilitiesTable()->getSkipActions($controllerName);
    }

    /**
     * Get list of controller's skipped actions.
     *
     * @param  string $controllerName Controller name
     * @deprecated
     * @return array
     */
    protected static function _getSkipActions($controllerName)
    {
        return static::getSkipActions($controllerName);
    }

    /**
     * Generate capability's controller name.
     *
     * @param  string $controllerName Controller name
     * @return string
     */
    protected static function _generateCapabilityControllerName($controllerName)
    {
        return static::_getCapabilitiesTable()->generateCapabilityControllerName($controllerName);
    }

    /**
     * Generate capability name.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    protected static function _generateCapabilityName($controllerName, $action)
    {
        return static::_getCapabilitiesTable()->generateCapabilityName($controllerName, $action);
    }

    /**
     * Generate capability label.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    protected static function _generateCapabilityLabel($controllerName, $action)
    {
        return static::_getCapabilitiesTable()->generateCapabilityLabel($controllerName, $action);
    }

    /**
     * Generate capability description.
     *
     * @param  string $controllerName Controller name
     * @param  string $action         Action name
     * @return string
     */
    protected static function _generateCapabilityDescription($controllerName, $action)
    {
        return static::_getCapabilitiesTable()->generateCapabilityDescription($controllerName, $action);
    }
}
