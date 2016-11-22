<?php
namespace RolesCapabilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\App;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Utility\Capability;

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
        $this->_capabilitiesTable->setCurrentRequest($config['currentRequest']);
        $this->_capabilitiesTable->setCurrentUser($this->Auth->user());
    }

    /**
     * @see RolesCapabilities\Utility\Capability::getAllCapabilities()
     * @deprecated
     * @return array
     */
    public function getAllCapabilities()
    {
        return Capability::getAllCapabilities();
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
     * @see RolesCapabilities\Utility\Capability::getControllers()
     * @param  bool  $includePlugins flag for including plugin controllers.
     * @deprecated
     * @return array
     */
    protected function _getAllControllers($includePlugins = true)
    {
        return Capability::getControllers();
    }

    /**
     * @see RolesCapabilities\Utility\Capability::getDirControllers()
     * @param  string $path   directory path
     * @param  string $plugin plugin name
     * @param  bool   $fqcn   flag for using fqcn
     * @deprecated
     * @return array
     */
    protected function _getDirControllers($path, $plugin = null, $fqcn = true)
    {
        return Capability::getDirControllers();
    }
}
