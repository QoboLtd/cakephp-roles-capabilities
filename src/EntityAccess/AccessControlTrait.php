<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\ORM\Table;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\PolicyBuilder;

/**
 * Trait to allow a class to perform authorization checks
 *
 */
trait AccessControlTrait
{
    /**
     * Checks authorization for access to this controller action.
     *
     * Please note that standard actions (create, read, update and delete are checked by the query filter)
     * @param Controller $controller The controller
     * @param string $action The action
     * @param ?string $entityId The id of the entity this operation is about (if applicable)
     * @return bool
     */
    public function authorizeControllerAction(Controller $controller, string $action, ?string $entityId): bool
    {
        $ctx = AuthorizationContextHolder::context();
        if ($ctx === null) {
            return true;
        }

        $table = $controller->loadModel();
        if (!($table instanceof Table)) {
            return true;
        }

        $builder = new PolicyBuilder($ctx->subject(), $table, $action, $entityId);
        AuthorizationContextHolder::asSystem();
        try {
            $policy = $builder->build();

            return $policy->allow();
        } finally {
            AuthorizationContextHolder::pop();
        }
    }
}
