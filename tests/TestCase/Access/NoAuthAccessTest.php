<?php

namespace RolesCapabilities\Test\TestCase\Access;

use Cake\Core\Configure;
use RolesCapabilities\Access\NoAuthAccess;

class NoAuthAccessTest extends \PHPUnit_Framework_TestCase
{
    public function testSkipControllers()
    {
        $skipControllers = (array)Configure::read('RolesCapabilities.ownerCheck.skipControllers');
        $noAuth = new NoAuthAccess();
        $this->assertArraySubset($noAuth->getSkipControllers(), $skipControllers);
    }

    public function testSkipActions()
    {
        $skipActions = (array)Configure::read('RolesCapabilities.ownerCheck.skipActions');
        $noAuth = new NoAuthAccess();
        $this->assertArraySubset($noAuth->getSkipActions(), $skipActions);
    }
}
