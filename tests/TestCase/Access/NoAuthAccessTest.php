<?php

namespace RolesCapabilities\Test\TestCase\Access;

use Cake\Core\Configure;
use PHPUnit\Framework\TestCase;
use RolesCapabilities\Access\NoAuthAccess;

/**
 * @property \RolesCapabilities\Access\NoAuthAccess $instance
 */
class NoAuthAccessTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->instance = new NoAuthAccess();
    }

    public function testSkipControllers(): void
    {
        $skipControllers = (array)Configure::read('RolesCapabilities.accessCheck.skipControllers');
        $noAuth = new NoAuthAccess();
        $this->assertArraySubset($noAuth->getSkipControllers(), $skipControllers);
    }

    public function testSkipActions(): void
    {
        $controller = 'CakeDC\Users\Controller\UsersController';
        $skipActions = (array)Configure::read('RolesCapabilities.accessCheck.skipActions');
        $noAuth = new NoAuthAccess();
        $this->assertArraySubset($noAuth->getSkipActions($controller), $skipActions[$controller]);
    }

    public function testHasAccess(): void
    {
        $user = [];
        $url = [
            'plugin' => 'CakeDC\Users',
            'controller' => 'Users',
            'action' => 'login',
        ];
        $this->assertTrue($this->instance->hasAccess($url, $user), 'No auth access to non restricted area');

        $url = [
            'plugin' => '',
            'controller' => 'Users',
            'action' => 'view',
        ];
        $this->assertFalse($this->instance->hasAccess($url, $user), 'No auth access to restricted area');
    }
}
