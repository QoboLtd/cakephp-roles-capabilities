<?php
declare(strict_types=1);

namespace RolesCapabilities\Auth;

use Cake\Auth\BaseAuthorize;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use RolesCapabilities\EntityAccess\AccessControlTrait;
use RolesCapabilities\EntityAccess\SubjectInterface;
use RolesCapabilities\EntityAccess\UserWrapper;

class EntityAccessAuthorize extends BaseAuthorize
{
    use AccessControlTrait;

    /**
     * Controller for the request.
     *
     * @var ?Controller
     */
    private $controller;

    /**
     * {@inheritDoc}
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->controller = $registry->getController();
    }

    /**
     * Checks user authorization.
     *
     * @param array|\ArrayAccess $user Active user data
     * @param \Cake\Http\ServerRequest $request Request instance.
     * @return bool
     */
    public function authorize($user, ServerRequest $request): bool
    {
        if ($this->controller === null) {
            return false;
        }

        if (!($user instanceof SubjectInterface)) {
            $user = UserWrapper::forUser($user);
        }

        $action = $request->getParam('action');

        $idParam = $this->getConfig('idParam', 'id');

        $entityId = $request->getParam($idParam, null);

        return $this->isControllerActionAuthorized($this->controller, $action, $entityId, $user);
    }
}
