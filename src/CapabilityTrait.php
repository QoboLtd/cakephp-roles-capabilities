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
namespace RolesCapabilities;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Access\AccessFactory;
use RolesCapabilities\Access\Utils;

trait CapabilityTrait
{

    /**
     * Returns permission capabilities.
     *
     * @param  string $controllerName Controller Name
     * @param  array  $actions        Controller actions
     * @return array
     */
    public static function getCapabilities($controllerName = null, array $actions = [])
    {
        return Utils::getCapabilities($controllerName, $actions);
    }

    /**
     * Check if current user has access to perform action.
     *
     * @param Event $url Event object
     * @param array $user User info
     * @return bool result of hasAccess method
     * @throws Cake\Network\Exception\ForbiddenException
     * @todo this needs re-thinking
     */
    protected function _checkAccess($url, $user)
    {
        $accessFactory = new AccessFactory();

        return $accessFactory->hasAccess($url, $user);
    }

    /**
     *  _getSkipActions method
     *
     * @param array $url user tries to access for
     * @return array list of actions to skip
     */
    protected function _getSkipActions($url)
    {
        $controller = Utils::normalizeControllerName($url);
        $skipActions = (array)Configure::read('RolesCapabilities.accessCheck.skipActions.' . $controller);

        return $skipActions;
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
     * managePermissions method
     *
     * Empty method just to have capability
     *
     * @return void
     */
    public function managePermissions()
    {
    }
}
