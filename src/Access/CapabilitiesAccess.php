<?php

namespace RolesCapabilities\Access;

use Cake\Core\App;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use ReflectionClass;
use ReflectionMethod;
use RolesCapabilities\Access\Utils;
use RolesCapabilities\Capability as Cap;

/**
 *  CapabilitiesAccess class checks if user has access to specific entity
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class CapabilitiesAccess implements AccessInterface
{
    /**
     * Capabilities Table instance.
     *
     * @var object
     */
    protected static $_capabilitiesTable;

    /**
     * User action specific capabilities
     *
     * @var array
     */
    protected $_userActionCapabilities = [];
    
    /**
     * Controller action(s) capabilities
     *
     * @var array
     */
    protected $_controllerActionCapabilites = [];
    
     /**
     * Non-assigned actions
     *
     * @var array
     */
    protected $_nonAssignedActions = [
        'add'
    ];

    /**
     *  CheckAccess Capabilities
     *
     * @param array $url    request URL
     * @param array $user   user's session data
     * @return bool         true or false
     */
    public function hasAccess($url, $user)
    {
        $controllerName = Utils::getControllerFullName($url);

        $actionCapabilities = [];
        if (!empty($url['action'])) {
            $actionCapabilities = $this->getCapabilities($controllerName, [$url['action']]);
        }

        // if action capabilities is empty, means that current controller or action are skipped
        if (empty($actionCapabilities)) {
            return true;
        }

        $hasAccess = $this->hasTypeAccess(Utils::getTypeFull(), $actionCapabilities, $user, $url);

        // if user has no full access capabilities
        if (!$hasAccess) {
            $hasAccess = $this->hasTypeAccess(Utils::getTypeOwner(), $actionCapabilities, $user, $url);
            if ($hasAccess) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }
    
    /**
     * Returns Controller permission capabilities.
     *
     * @param  string $controllerName Controller name
     * @param  array  $actions        Controller actions
     * @return array
     */
    public function getCapabilities($controllerName = null, array $actions = [])
    {
        $result = [];

        if (is_null($controllerName) || !is_string($controllerName)) {
            return $result;
        }

        $skipControllers = [];
        if (is_callable([$controllerName, 'getSkipControllers'])) {
            $skipControllers = $controllerName::getSkipControllers();
        }

        if (in_array($controllerName, $skipControllers)) {
            return $result;
        }

        $actions = Utils::getActions($controllerName, $actions);

        if (empty($actions)) {
            return $result;
        }

        // get controller table instance
        $controllerTable = Utils::getControllerTableInstance($controllerName);

        return $this->_getCapabilities(
            Utils::generateCapabilityControllerName($controllerName),
            $actions,
            static::_getCapabilitiesTable()->getTableAssignationFields($controllerTable)
        );
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
    public function hasTypeAccess($type, array $actionCapabilities, array $user, array $url)
    {
        // skip if action has no access capabilities for specified type
        if (!isset($actionCapabilities[$type])) {
            return false;
        }

        foreach ($actionCapabilities[$type] as $actionCapability) {
            // user has access
            if ($this->hasAccessInCapabilities($actionCapability->getName(), $user['id'])) {
                // store in user's action capabilities
                $this->setUserActionCapability(
                    $url['plugin'],
                    $url['controller'],
                    $url['action'],
                    $type,
                    $actionCapability
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Method that checks if current user is allowed access.
     * Returns true if current user has access, false otherwise.
     * @param  string $capability capability name
     * @param  string $userId     user id
     * @return bool
     */
    public function hasAccessInCapabilities($capability, $userId)
    {
        $userCaps = static::_getCapabilitiesTable()->getUserCapabilities($userId);
        if (in_array($capability, $userCaps)) {
            return true;
        }

        return false;
    }

    /**
     * User action capability setter.
     *
     * @param  string                        $plugin     Plugin name
     * @param  string                        $controller Controller name
     * @param  string                        $action     Action type
     * @param  string                        $type       Capability type
     * @param  \RolesCapabilities\Capability $capability Capability instance
     * @return void
     */
    public function setUserActionCapability($plugin, $controller, $action, $type, Cap $capability)
    {
        $this->_userActionCapabilities[$plugin][$controller][$action][$type][] = $capability;
    }

    /**
     * User action capabilities getter.
     *
     * @return array
     */
    public function getUserActionCapabilities()
    {
        return $this->_userActionCapabilities;
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

    /**
     * Method that generates capabilities for specified controller's actions.
     * Capabilities included are full or owner access types.
     *
     * @param  string $controllerName    Controller name
     * @param  array  $actions           Controller actions
     * @param  array  $assignationFields Table assignation fields (example: assigned_to)
     * @return array
     */
    protected function _getCapabilities($controllerName, array $actions, array $assignationFields = [])
    {
        $key = implode('.', $actions);
        if (!empty($this->_controllerActionCapabilites[$controllerName][$key])) {
            return $this->_controllerActionCapabilites[$controllerName][$key];
        }

        $result = [];
        foreach ($actions as $action) {
            // generate action's full (all) type capabilities
            $result[Utils::CAP_TYPE_FULL][] = new Cap(
                Utils::generateCapabilityName($controllerName, $action),
                [
                    'label' => Utils::generateCapabilityLabel($controllerName, $action . '_all'),
                    'description' => Utils::generateCapabilityDescription(
                        $controllerName,
                        Utils::humanizeActionName($action)
                    )
                ]
            );
            // skip rest of the logic if assignment fields are not found
            // or if current action does not support assignment (Example: add / create)
            if (empty($assignationFields) || in_array($action, $this->_nonAssignedActions)) {
                continue;
            }

            // generate action's owner (assignment field) type capabilities
            foreach ($assignationFields as $assignationField) {
                $result[Utils::CAP_TYPE_OWNER][] = new Cap(
                    Utils::generateCapabilityName($controllerName, $action . '_' . $assignationField),
                    [
                        'label' => Utils::generateCapabilityLabel($controllerName, $action . '_' . $assignationField),
                        'description' => Utils::generateCapabilityDescription(
                            $controllerName,
                            Utils::humanizeActionName($action) . ' if owner (' . Inflector::humanize($assignationField) . ')'
                        ),
                        'field' => $assignationField
                    ]
                );
            }
        }

        $this->_controllerActionCapabilites[$controllerName][$key] = $result;

        return $this->_controllerActionCapabilites[$controllerName][$key];
    }

}
