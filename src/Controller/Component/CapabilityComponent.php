<?php
namespace RolesCapabilities\Controller\Component;

use Cake\Controller\Component;
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
     * Initialize method
     * @param  array  $config configuration array
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_controller = $this->_registry->getController();
        $this->_user = $this->Auth->user();
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

        $userCaps = $this->_getUserCapabilities($userId);
        if (in_array($capability, $userCaps)) {
            return true;
        }

        return false;
    }

    /**
     * Method that retrieves specified user's capabilities
     * @param  string $userId user id
     * @return array
     */
    protected function _getUserCapabilities($userId)
    {
        $userGroups = $this->Group->getUserGroups($userId);

        $userRoles = [];
        if (!empty($userGroups)) {
            $userRoles = $this->_getGroupsRoles($userGroups);
        }

        $userCaps = [];
        if (!empty($userRoles)) {
            $rolesCaps = TableRegistry::get('RolesCapabilities.Capabilities');
            $query = $rolesCaps->find('list')->where(['role_id IN' => array_keys($userRoles)]);
            $userCaps = $query->toArray();
        }

        return array_values($userCaps);
    }

    /**
     * Method that retrieves specified group(s) roles.
     * @param  array  $userGroups group(s) id(s)
     * @return array
     */
    protected function _getGroupsRoles(array $userGroups = [])
    {
        $result = [];

        if (!empty($userGroups)) {
            $roles = TableRegistry::get('RolesCapabilities.Roles');

            $query = $roles->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ]);
            $query->matching('Groups', function ($q) use ($userGroups) {
                return $q->where(['Groups.id IN' => array_keys($userGroups)]);
            });
            $result = $query->toArray();
        }

        return $result;
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
            $classObj = new $controller;
            if (method_exists($classObj, 'getCapabilities')) {
                foreach ($classObj->getCapabilities() as $capability) {
                    $capabilities[$controller][$capability->getName()] = $capability->getLabel();
                }
            }
        }

        return $capabilities;
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
                        $className = \Cake\Core\App::className($className, 'Controller');
                    }

                    $controllers[] = $className;
                }
            }
        }

        return $controllers;
    }
}
