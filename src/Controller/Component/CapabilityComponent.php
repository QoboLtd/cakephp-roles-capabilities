<?php
namespace RolesCapabilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\App;
use Cake\ORM\TableRegistry;

class CapabilityComponent extends Component
{
    /**
     * Allow flag
     */
    const ALLOW = true;

    /**
     * Deny flag
     */
    const DENY = false;

    public $components = ['Auth', 'Groups.Group'];

    /**
     * Current controller
     * @var object
     */
    protected $_controller;

    /**
     * Current user details
     * @var array
     */
    protected $_user = [];

    /**
     * Capabilities Table instance.
     *
     * @var object
     */
    protected $_capabilitiesTable;

    /**
     * Initialize method
     * @param  array  $config configuration array
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_controller = $this->_registry->getController();
        $this->_user = $this->Auth->user();
        $this->_capabilitiesTable = TableRegistry::get('RolesCapabilities.Capabilities');
    }

    /**
     * Method that retrieves all defined capabilities
     * @return array capabilities
     */
    public function getAllCapabilities()
    {
        $capabilities = [];
        // get all controllers
        $controllers = $this->_getAllControllers();

        foreach ($controllers as $controller) {
            if (is_callable([$controller, 'getCapabilities'])) {
                foreach ($controller::getCapabilities($controller) as $capability) {
                    $capabilities[$controller][$capability->getName()] = $capability->getDescription();
                }
            }
        }

        return $capabilities;
    }

    /**
     * Method that checks if current user is allowed access.
     * Returns true if current user has access, false otherwise.
     * @param  string $capability capability name
     * @param  string $userId     user id
     * @return bool
     */
    public function hasAccess($capability, $userId = '')
    {
        // if not specified, get current user's id
        if (empty($userId)) {
            $userId = $this->_user['id'];
        }

        return $this->_capabilitiesTable->hasAccess($capability, $userId);
    }

    /**
     * Method that checks if specified role is allowed access.
     * Returns true if role has access, false otherwise.
     *
     * @param  string $roleId role id
     * @param  string $userId user id
     * @return bool
     */
    public function hasRoleAccess($roleId, $userId = '')
    {
        // if not specified, get current user's id
        if (empty($userId)) {
            $userId = $this->_user['id'];
        }

        return $this->_capabilitiesTable->hasRoleAccess($roleId, $userId);
    }

    /**
     * Method that retrieves specified user's capabilities
     * @param  string $userId user id
     * @deprecated
     * @return array
     */
    protected function _getUserCapabilities($userId)
    {
        return $this->_capabilitiesTable->getUserCapabilities($userId);
    }

    /**
     * Method that retrieves specified group(s) roles.
     * @param  array  $userGroups group(s) id(s)
     * @deprecated
     * @return array
     */
    protected function _getGroupsRoles(array $userGroups = [])
    {
        return $this->_capabilitiesTable->getGroupsRoles($userGroups);
    }

    /**
     * Method that returns all controller names.
     * @param  bool  $includePlugins flag for including plugin controllers
     * @return array                 controller names
     */
    protected function _getAllControllers($includePlugins = true)
    {
        $controllers = $this->_getDirControllers(APP . 'Controller' . DS);

        if (true === $includePlugins) {
            $plugins = \Cake\Core\Plugin::loaded();
            foreach ($plugins as $plugin) {
                // plugin path
                $path = \Cake\Core\Plugin::path($plugin) . 'src' . DS . 'Controller' . DS;
                $controllers = array_merge($controllers, $this->_getDirControllers($path, $plugin));
            }
        }

        return $controllers;
    }

    /**
     * Method that retrieves controller names
     * found on the provided directory path.
     * @param  string $path   directory path
     * @param  string $plugin plugin name
     * @param  bool   $fqcn   flag for using fqcn
     * @return array          controller names
     */
    protected function _getDirControllers($path, $plugin = null, $fqcn = true)
    {
        $controllers = [];
        if (file_exists($path)) {
            $dir = new \DirectoryIterator($path);
            foreach ($dir as $fileinfo) {
                $className = $fileinfo->getBasename('.php');
                if ($fileinfo->isFile() && 'AppController' !== $className) {
                    if (!empty($plugin)) {
                        $className = $plugin . '.' . $className;
                    }

                    if (true === $fqcn) {
                        $className = App::className($className, 'Controller');
                    }

                    $controllers[] = $className;
                }
            }
        }

        return $controllers;
    }
}
