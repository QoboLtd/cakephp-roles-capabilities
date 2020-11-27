<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
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
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $user = $this->Auth->user();

        if (!empty($user)) {
            AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), $this->getRequest()));
        } else {
            AuthorizationContextHolder::push(AuthorizationContext::asAnonymous($this->getRequest()));
        }

        return null;
    }

    public function afterFilter(Event $event)
    {
        AuthorizationContextHolder::pop();

        parent::afterFilter($event);

        return null;
    }
}
