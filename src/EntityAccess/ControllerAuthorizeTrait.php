<?php
declare(strict_types=1);

namespace RoleCapabilities\EntityAccess;

trait ControllerAuthorizeTrait
{
    use AccessControlTrait;

    /**
     * Gets the entityId from the request parameter 'id'
     * Override to change where the id comes from.
     */
    protected function getEntityId(): ?string
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Checks whether this action is authorized
     */
    public function isAuthorized($user = null): bool
    {
        $request = $this->getRequest();

        $action = $request->getParam('action');
        $op = Operation::value();
        if ($op === null) {
            $op = $action;
        }

        $entityId = $this->getEntityId();

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), $request));
        try {
            return $this->authorizeControllerAction($this, $action, $entityId);
        } finally {
            AuthorizationContextHolder::pop();
        }
    }
}
