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
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use ReflectionClass;
use ReflectionMethod;
use RolesCapabilities\Access\Utils;
use RolesCapabilities\Capability as Cap;

/**
 *  CapabilitiesAccess class checks if user has access to specific entity
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class CapabilitiesAccess extends AuthenticatedAccess
{
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
     * All user capabilities
     *
     * @var array
     */
    protected $_userCapabilities = [];

    /**
     * Parent Access logic targeted actions.
     *
     * @var array
     * @todo This is a temporary fix until proper model / controller specific capabilities are implemented.
     */
    protected $_parentAccessActions = ['index', 'view'];

    /**
     *  CheckAccess Capabilities
     *
     * @param array $url    request URL
     * @param array $user   user's session data
     * @return bool         true or false
     */
    public function hasAccess($url, $user)
    {
        $result = parent::hasAccess($url, $user);
        if (!$result) {
            return false;
        }

        $controllerName = Utils::getControllerFullName($url);

        $actionCapabilities = [];
        if (!empty($url['action'])) {
            $actionCapabilities = Utils::getCapabilities($controllerName, [$url['action']]);
        }

        // if action capabilities is empty, means that current controller or action are skipped
        if (empty($actionCapabilities)) {
            return true;
        }

        // User has full access
        $hasAccess = Utils::hasTypeAccess(Utils::getTypeFull(), $actionCapabilities, $user, $url);
        if ($hasAccess) {
            return true;
        }

        // if user has no full access capabilities
        $hasAccess = Utils::hasTypeAccess(Utils::getTypeOwner(), $actionCapabilities, $user, $url);
        if ($hasAccess) {
            return true;
        }

        if (in_array($url['action'], $this->_parentAccessActions)) {
            $hasAccess = $this->_hasParentAccess($url, $user);
            if ($hasAccess) {
                return true;
            }
        }

        return false;
    }

    /**
     *  _hasParentAccess method
     *
     * @param array $url    request URL
     * @param array $user   user's session data
     * @return bool         true or false
     */
    protected function _hasParentAccess($url, $user)
    {
        $config = new ModuleConfig(ConfigType::MODULE(), Inflector::camelize($url['controller']));
        $moduleConfig = (array)json_decode(json_encode($config->parse()), true);

        $parents = $moduleConfig['table']['permissions_parent_modules'];

        return !empty($parents);
    }

    /**
     * Method that retrieves specified user's capabilities
     * @param  string $userId user id
     * @return array
     */
    public function getUserCapabilities($userId)
    {
        if (empty($this->_userCapabilities)) {
            $this->_userCapabilities = Utils::fetchUserCapabilities($userId);
        }

        return $this->_userCapabilities;
    }
}
