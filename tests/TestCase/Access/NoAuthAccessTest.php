<?php

namespace RolesCapabilities\Test\TestCase\Access;

use Cake\Core\Configure;
use RolesCapabilities\Access\NoAuthAccess;

class NoAuthAccessTest extends \PHPUnit_Framework_TestCase
{
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
}
