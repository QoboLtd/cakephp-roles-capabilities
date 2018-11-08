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

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use RolesCapabilities\Access\AccessFactory;
use RolesCapabilities\Access\Utils;

trait CapabilityTrait
{

    /**
     * Returns permission capabilities.
     *
     * @param  string $controllerName Controller Name
     * @param  mixed[]  $actions        Controller actions
     * @return mixed[]
     */
    public static function getCapabilities(string $controllerName = null, array $actions = []): array
    {
        return Utils::getCapabilities($controllerName, $actions);
    }

    /**
     * Check if current user has access to perform action.
     *
     * @param mixed[] $url URL
     * @param mixed[] $user User info
     * @return bool result of hasAccess method
     * @todo this needs re-thinking
     */
    protected function _checkAccess(array $url, array $user): bool
    {
        $accessFactory = new AccessFactory();

        return $accessFactory->hasAccess($url, $user);
    }

    /**
     *  _getSkipActions method
     *
     * @param mixed[] $url user tries to access for
     * @return mixed[] list of actions to skip
     */
    protected function _getSkipActions(array $url): array
    {
        $controller = Utils::normalizeControllerName($url);
        $skipActions = (array)Configure::read('RolesCapabilities.accessCheck.skipActions.' . $controller);

        return $skipActions;
    }

    /**
     * managePermissions method
     *
     * Empty method just to have capability
     *
     * @return void
     */
    public function managePermissions(): void
    {
    }
}
