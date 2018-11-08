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

use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 *  CapabilitiesAccess class checks if user has access to specific entity
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class CapabilitiesAccess extends AuthenticatedAccess
{
    /**
     * All user capabilities
     *
     * @var array
     */
    protected $userCapabilities = [];

    /**
     * Parent Access logic targeted actions.
     *
     * @var array
     * @todo This is a temporary fix until proper model / controller specific capabilities are implemented.
     */
    protected $parentAccessActions = ['index', 'view'];

    /**
     *  CheckAccess Capabilities
     *
     * @param mixed[] $url    request URL
     * @param mixed[] $user   user's session data
     * @return bool         true or false
     */
    public function hasAccess(array $url, array $user): bool
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

        $hasAccess = Utils::hasTypeAccess(Utils::getTypeBelongs(), $actionCapabilities, $user, $url);
        if ($hasAccess) {
            return true;
        }

        if (in_array($url['action'], $this->parentAccessActions)) {
            $hasAccess = $this->hasParentAccess($url);
            if ($hasAccess) {
                return true;
            }
        }

        return false;
    }

    /**
     *  hasParentAccess method
     *
     * @param mixed[] $url    request URL
     * @return bool         true or false
     */
    protected function hasParentAccess(array $url): bool
    {
        $config = new ModuleConfig(ConfigType::MODULE(), Inflector::camelize($url['controller']));
        $moduleConfig = $config->parseToArray();

        $parents = $moduleConfig['table']['permissions_parent_modules'];

        return !empty($parents);
    }

    /**
     * Method that retrieves specified user's capabilities
     * @param  string $userId user id
     * @return mixed[]
     */
    public function getUserCapabilities(string $userId): array
    {
        if (empty($this->userCapabilities)) {
            $this->userCapabilities = Utils::fetchUserCapabilities($userId);
        }

        return $this->userCapabilities;
    }
}
