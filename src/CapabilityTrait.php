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
     * @param  array  $actions        Controller actions
     * @return array
     */
    public static function getCapabilities($controllerName = null, array $actions = [])
    {
        return static::_getCapabilitiesTable()->getCapabilities($controllerName, $actions);
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
        static::_getCapabilitiesTable()->checkAccess(
            $event->subject()->request->params,
            $this->Auth->user()
        );
    }

    /**
     * Check if specified role has access to perform action.
     *
     * @param  string  $role role uuid
     * @param  bool    $handle handle
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
     * Get list of skipped controllers.
     *
     * @return array
     */
    public static function getSkipControllers()
    {
        return static::_getCapabilitiesTable()->getSkipControllers();
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
     * Generate capability's controller name.
     *
     * @param  string $controllerName Controller name
     * @return string
     */
    protected static function _generateCapabilityControllerName($controllerName)
    {
        return static::_getCapabilitiesTable()->generateCapabilityControllerName($controllerName);
    }
}
