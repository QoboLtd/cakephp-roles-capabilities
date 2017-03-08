<?php
namespace RolesCapabilities;

use Cake\Core\App;
use Cake\Event\Event;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Access\AccessFactory;
use RolesCapabilities\Access\CapabilitiesAccess;

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
        $capabilitiesAccess = new CapabilitiesAccess();

        return $capabilitiesAccess->getCapabilities($controllerName, $actions);
    }

    /**
     * Check if current user has access to perform action.
     *
     * @param  Event    $url Event object
     * @return bool     result of hasAccess method
     * @throws Cake\Network\Exception\ForbiddenException
     * @todo                 this needs re-thinking
     */
    protected function _checkAccess($url, $user)
    {
        $accessFactory = new AccessFactory();

        return $accessFactory->hasAccess($url, $user);
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
}
