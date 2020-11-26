<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\App\Controller;

use Cake\Controller\Controller;

class AppController extends Controller
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Auth', [
            'authenticate' => ['Form'],
        ]);

        $this->loadComponent('Flash');
        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
    }
}
