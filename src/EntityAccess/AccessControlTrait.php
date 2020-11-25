<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Controller\Controller;
use Cake\ORM\Table;

trait AccessControlTrait
{
    /**
     * Checks whether this action is authorized.
     * It uses isTableActionAuthorized for authorization.
     *
     * @param Controller $controller The controller to check
     * @param string $action The action to check
     * @param ?string $entityId The entity to check
     * @param ?SubjectInterface $user The user
     *
     * @return bool
     */
    public function isControllerActionAuthorized(Controller $controller, string $action, ?string $entityId, ?SubjectInterface $user = null): bool
    {
        $table = $controller->loadModel();
        if (!($table instanceof Table)) {
            return false;
        }

        return $this->isTableActionAuthorized($table, $action, $entityId, $user);
    }

    /**
     * Checks whether this action is authorized
     *
     * @param Table $table The table to check
     * @param string $action The action to check
     * @param ?string $entityId The entity Id
     * @param ?SubjectInterface $user The user
     *
     * @return bool
     */
    public function isTableActionAuthorized(Table $table, string $action, ?string $entityId, ?SubjectInterface $user = null): bool
    {
        $op = Operation::value($action);
        if ($op === null) {
            $op = $action;
        }

        AuthorizationContextHolder::asSystem();
        try {
            $builder = new PolicyBuilder($user, $table, $action, $entityId);
            $policy = $builder->build();

            return $policy->allow();
        } finally {
            AuthorizationContextHolder::pop();
        }
    }
}
