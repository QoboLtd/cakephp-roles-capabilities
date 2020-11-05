<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Controller\Controller;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\PolicyBuilder;

trait AccessControlTrait
{
    /**
     * Checks authorization for access to this controller action.
     * @param Controller $controller The controller to be accessed
     * @param string $action The action on the controller
     * @return bool
     */
    public function authorizeAccess(Controller $controller, string $action): bool
    {
        $ctx = AuthorizationContextHolder::context();
        if ($ctx === null) {
            return null;
        }

        $resource = $controller->getName();

        if ($controller->getPlugin() !== null) {
            $resource = $controller->getPlugin() . '/' . $resource;
        }

        $builder = new ResourcePolicyBuilder($ctx->subject(), $resource, $action);

        AuthorizationContextHolder::asSystem();
        try {
            $resourcePolicy = $builder->build();

            return $resourcePolicy->allow();
        } finally {
            AuthorizationContextHolder::pop();
        }
    }
}
