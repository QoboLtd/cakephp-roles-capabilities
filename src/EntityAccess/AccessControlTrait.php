<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Controller\Controller;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\PolicyBuilder;

trait AccessControlTrait
{
    public function authorizeAccess(Controller $controller): bool
    {
        $ctx = AuthorizationContextHolder::context();
        if ($ctx === null) {
            return null;
        }

        $table = $controller->loadModel();
        if ($table === null) {
            return null;
        }

        if (!($table instanceof Table)) {
            return null;
        }

        $builder = new PolicyBuilder($ctx->subject(), $table, $operation, $entityId);

        AuthorizationContextHolder::asSystem();
        try {
            $ormPolicy = $builder->build();

            return $ormPolicy->allow();
        } finally {
            AuthorizationContextHolder::pop();
        }
    }
}