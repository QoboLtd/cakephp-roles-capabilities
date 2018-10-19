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
namespace Qobo\RolesCapabilities\Access;

use Cake\Core\Configure;

/**
 *  NoAuthAccess class
 *
 *  Contains logic to check skip controllers and actions
 */
class NoAuthAccess extends BaseAccessClass
{
    /**
     *  Skip controllers
     */
    protected $skipControllers = [];

    /**
     *  Skip actions
     */
    protected $skipActions = [];

    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->skipControllers = (array)Configure::read('RolesCapabilities.accessCheck.skipControllers');
        $this->skipActions = (array)Configure::read('RolesCapabilities.accessCheck.skipActions');
    }

    /**
     *  hasAccess()
     *
     *  check if access is allowed for that action and user
     *
     * @param array $url    URL accessed by user
     * @param array $user   user's session data
     * @return array        true in case of action should be skip or false otherwise
     *
     */
    public function hasAccess($url, $user)
    {
        if (!empty($url['action']) && !empty($url['controller'])) {
            $controllerName = Utils::normalizeControllerName($url);
            if ($this->isSkipAction($controllerName, $url['action'])) {
                return true;
            }
        }

        if (!empty($url['controller']) && $this->isSkipController($url['controller'])) {
            return true;
        }

        if (!empty($url['action']) && $this->isSkipControllerActions($url['controller'], $url['action'])) {
            return true;
        }

        return false;
    }

    /**
     *  getSkipActions()
     *
     *  returns a list of actions which should be skipped
     *
     * @param string $controller the user tried to access
     * @return array    list of skipped actions
     */
    public function getSkipActions($controller)
    {
        return !empty($this->skipActions[$controller]) ? $this->skipActions[$controller] : [];
    }

    /**
     *  getSkipControllers()
     *
     *  returns a list of skipped controllers
     *
     * @return array    list of skipped controllers
     */
    public function getSkipControllers()
    {
        return $this->skipControllers;
    }

    /**
     *  isSkipController()
     *
     *  check if given controller should be skipped
     *
     * @param string $controller    controller the user tries to access
     * @return bool                 true if controller should be skipped, false otherwise
     */
    protected function isSkipController($controller)
    {
        if (in_array($controller, $this->getSkipControllers())) {
            return true;
        }

        return false;
    }

    /**
     *  isSkipAction()
     *
     *  checks if given action should be skipped
     *
     * @param string $controller    the user tries to access
     * @param string $action        the user tries to access
     * @return bool                 true if action is empty or in the list of skip actions, false if not
     *
     */
    protected function isSkipAction($controller, $action)
    {
        if (in_array($action, $this->getSkipActions($controller))) {
            return true;
        }

        return false;
    }

    /**
     *  isSkipControllerActions()
     *
     *  s if given action is in the list of controller's skip actions
     *
     * @param string $controllerName    Controller the user tries to access for
     * @param string $action            Action the user tries to access for
     * @return bool                     true in case of action is in controller's skip action list and false if not
     */
    protected function isSkipControllerActions($controllerName, $action)
    {
        $skipActions = [];
        if (is_callable([$controllerName, 'getSkipActions'])) {
            $skipActions = $controllerName::getSkipActions($controllerName);
        }

        if (in_array($action, $skipActions)) {
            return true;
        }

        return false;
    }
}
