<?php

namespace RolesCapabilities\Access;

use Cake\Core\Configure;

/**
 *  NoAuthAccess class
 *
 *  Contains logic to check skip controllers and actions
 */
class NoAuthAccess extends AccessBaseClass
{
    /**
     *  Skip controllers
     */
    protected $_skipControllers = [
        'CakeDC\Users\Controller\SocialAccountsController',
        'App\Controller\PagesController'
    ];

    /**
     *  Skip actions
     */
    protected $_skipActions = [
        'login', 'logout'
    ];

    /**
     *  Constructor
     *
     */
    public function __construct()
    {
        // merge controllers to be skipped from app's configuration
        $skipControllers = Configure::read('RolesCapabilities.skip_controllers');
        $this->_skipControllers = array_merge(
            $this->_skipControllers,
            is_null($skipControllers) ? [] : $skipControllers
        );
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
        if (!empty($url['action']) && $this->_isSkipAction($url['action'])) {
            return true;
        }

        if (!empty($url['controller']) && $this->_isSkipController($url['controller'])) {
            return true;
        }

        if (!empty($url['action']) && $this->_isSkipControllerActions($url['controller'], $url['action'])) {
            return true;
        }

        return false;
    }

    /**
     *  _getSkipActions()
     *
     *  returns a list of actions which should be skipped
     *
     * @return array    list of skipped actions
     */
    protected function _getSkipActions()
    {
        return $this->_skipActions;
    }

    /**
     *  _getSkipControllers()
     *
     *  returns a list of skipped controllers
     *
     * @return array    list of skipped controllers
     */
    protected function _getSkipControllers()
    {
        return $this->_skipControllers;
    }

    /**
     *  _isSkipController()
     *
     *  check if given controller should be skipped
     *
     * @param string $controller    controller the user tries to access
     * @return bool                 true if controller should be skipped, false otherwise
     */
    protected function _isSkipController($controller)
    {
        if (in_array($controller, $this->_getSkipControllers())) {
            return true;
        }

        return false;
    }

    /**
     *  _isSkipAction()
     *
     *  checks if given action should be skipped
     *
     * @param string $action    action the user tries to access
     * @return bool             true if action is empty or in the list of skip actions, false if not
     *
     */
    protected function _isSkipAction($action)
    {
        if (in_array($action, $this->_getSkipActions())) {
            return true;
        }

        return false;
    }

    /**
     *  _isSkipControllerActions()
     *
     *  s if given action is in the list of controller's skip actions
     *
     * @param string $controllerName    Controller the user tries to access for
     * @param string $action            Action the user tries to access for
     * @return bool                     true in case of action is in controller's skip action list and false if not
     */
    protected function _isSkipControllerActions($controllerName, $action)
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