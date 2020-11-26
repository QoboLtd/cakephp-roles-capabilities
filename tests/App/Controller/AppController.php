<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\App\Controller;

use Cake\Controller\Controller;
use RolesCapabilities\EntityAccess\AuthorizationContext;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\UserWrapper;

class AppController extends Controller
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Auth', [
            'authenticate' => ['Form'],
            'authorize' => [
                'RolesCapabilities.EntityAccess',
            ],
            'unauthorizedRedirect' => false,
        ]);

        $this->loadComponent('Flash');
        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);

        $user = $this->Auth->user();

        if (!empty($user)) {
            AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), $this->getRequest()));
        }
    }
}
