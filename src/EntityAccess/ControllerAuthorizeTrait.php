<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Controller\Controller;

trait ControllerAuthorizeTrait
{
    use AccessControlTrait;

    /**
     * Checks whether this action is authorized
     *
     * @param Controller $controller The controller to check
     * @param string $action The action to check
     * @param ?mixed $user The user
     *
     * @return bool
     */
    public function isActionAuthorized(Controller $controller, string $action, $user = null): bool
    {
        $request = $controller->getRequest();

        $op = Operation::value($action);
        if ($op === null) {
            $op = $action;
        }

        $entityId = $controller->getRequest()->getParam('id');

        if ($user === null) {
            AuthorizationContextHolder::push(AuthorizationContext::asAnonymous($request));
        } else {
            AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), $request));
        }
        try {
            return $this->authorizeControllerAction($controller, $action, $entityId);
        } finally {
            AuthorizationContextHolder::pop();
        }
    }
}
