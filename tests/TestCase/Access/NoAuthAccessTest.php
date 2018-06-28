<?php

namespace RolesCapabilities\Test\TestCase\Access;

use Cake\Core\Configure;
use RolesCapabilities\Access\NoAuthAccess;

class NoAuthAccessTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->instance = new NoAuthAccess();
    }

    public function testSkipControllers()
    {
        $skipControllers = (array)Configure::read('RolesCapabilities.accessCheck.skipControllers');
        $noAuth = new NoAuthAccess();
        $this->assertArraySubset($noAuth->getSkipControllers(), $skipControllers);
    }

    public function testSkipActions()
    {
        $controller = 'CakeDC\Users\Controller\UsersController';
        $skipActions = (array)Configure::read('RolesCapabilities.accessCheck.skipActions');
        $noAuth = new NoAuthAccess();
        $this->assertArraySubset($noAuth->getSkipActions($controller), $skipActions[$controller]);
    }

    public function testHasAccess()
    {
        $user = [];
        $url = [
            'plugin' => 'CakeDC\Users',
            'controller' => 'Users',
            'action' => 'login'
        ];
        $this->assertTrue($this->instance->hasAccess($url, $user), 'No auth access to non restricted area');

        $url = [
            'plugin' => '',
            'controller' => 'Users',
            'action' => 'view'
        ];
        $this->assertFalse($this->instance->hasAccess($url, $user), 'No auth access to restricted area');
    }
}
