<?php

namespace RolesCapabilities\CheckAccess;

use Cake\Core\App;
use Cake\ORM\TableRegistry;
use ReflectionClass;
use ReflectionMethod;
use RolesCapabilities\Capability as Cap;
use Cake\Utility\Inflector;

class CapabilitiesCheckAccess implements CheckAccessInterface
{
    /**
     * Capabilities Table instance.
     *
     * @var object
     */
    protected static $_capabilitiesTable;


    /**
     *  CheckAccess Capabilities
     *
     * @param array $url    request URL
     * @param array $user   user's session data
     * @return boolean      true or false
     */
     public function checkAccess($url, $user)
     {
        
        $controllerName = static::_getCapabilitiesTable()->getControllerFullName($url);

        $actionCapabilities = [];
        if (!empty($url['action'])) {
            $actionCapabilities = static::_getCapabilitiesTable()->getCapabilities($controllerName, [$url['action']]);
        }
        
        // if action capabilities is empty, means that current controller or action are skipped
        if (empty($actionCapabilities)) {
            return true;
        }

        $hasAccess = static::_getCapabilitiesTable()->hasTypeAccess(static::_getCapabilitiesTable()->getTypeFull(), $actionCapabilities, $user, $url);
        
        // if user has no full access capabilities
        if (!$hasAccess) {
            $hasAccess = static::_getCapabilitiesTable()->hasTypeAccess(static::_getCapabilitiesTable()->getTypeOwner(), $actionCapabilities, $user, $url);
            if ($hasAccess) {
                return true;
            }
        } else {
            return true;
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
        if (empty(static::$_capabilitiesTable)) {
            static::$_capabilitiesTable = TableRegistry::get('RolesCapabilities.Capabilities');
        }

        return static::$_capabilitiesTable;
    }
}
