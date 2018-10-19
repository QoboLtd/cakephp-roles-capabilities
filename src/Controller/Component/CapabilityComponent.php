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
namespace Qobo\RolesCapabilities\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Qobo\RolesCapabilities\Access\Utils;

trigger_error(
    sprintf(
        'Use %s directly for access checks and %s for retrieving capabilities, instead of %s.',
        'Qobo\RolesCapabilities\Access\AccessFactory',
        'Qobo\RolesCapabilities\Access\Utils',
        'Qobo\RolesCapabilities\Controller\Component\CapabilityComponent'
    ),
    E_USER_DEPRECATED
);
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
        $this->_capabilitiesTable = TableRegistry::get('Qobo/RolesCapabilities.Capabilities');
    }

    /**
     * @see \RolesCapabilities\Access\Utils::getAllCapabilities()
     * @deprecated 16.3.1 use \RolesCapabilities\Access\Utils::getAllCapabilities()
     * @return array
     */
    public function getAllCapabilities()
    {
        trigger_error(
            sprintf(
                '%s() is deprecated. Use Qobo\RolesCapabilities\Access\Utils::getAllCapabilities() instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return Utils::getAllCapabilities();
    }

    /**
     * Method that checks if current user is allowed access.
     * Returns true if current user has access, false otherwise.
     * @param  string $capability capability name
     * @param  string $userId     user id
     * @return bool
     * @deprecated 16.3.1 use \RolesCapabilities\Access\AccessFactory::hasAccess()
     */
    public function hasAccess($capability, $userId = '')
    {
        trigger_error(
            sprintf(
                '%s() is deprecated. Use RolesCapabilities\Access\AccessFactory::hasAccess() instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

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
     * @deprecated 16.3.1 use \RolesCapabilities\Access\AccessFactory::hasAccess()
     */
    public function hasRoleAccess($roleId, $userId = '')
    {
        trigger_error(
            sprintf(
                '%s() is deprecated. Use RolesCapabilities\Access\AccessFactory::hasAccess() instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        // if not specified, get current user's id
        if (empty($userId)) {
            $userId = $this->_user['id'];
        }

        return $this->_capabilitiesTable->hasRoleAccess($roleId, $userId);
    }
}
