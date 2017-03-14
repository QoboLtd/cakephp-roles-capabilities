<?php

namespace RolesCapabilities\Test\TestCase\Access;

use Cake\Core\Configure;
use RolesCapabilities\Access\NoAuthAccess;

class NoAuthAccessTest extends \PHPUnit_Framework_TestCase
{
    public function testSkipControllers()
    {
        // TODO: add proper way to read config in test
        //$skipControllers = (array)Configure::read('RolesCapabilities.ownerCheck.skipControllers');
        //$noAuth = new NoAuthAccess();
        //$this->assertArraySubset($noAuth->getSkipControllers(), $skipControllers);
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testSkipActions()
    {
        // TODO: add proper way to read config in test
        //$skipActions = (array)Configure::read('RolesCapabilities.ownerCheck.skipActions');
        //$noAuth = new NoAuthAccess();
        //$this->assertArraySubset($noAuth->getSkipActions(), $skipActions);
        $this->markTestIncomplete('Not implemented yet.');
    }
}
