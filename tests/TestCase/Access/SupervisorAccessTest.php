<?php

namespace RolesCapabilities\Test\Access;

use RolesCapabilities\Access\SupervisorAccess;

class SupervisorAccessTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->instance = new SupervisorAccess();
    }

    public function testHasAccess()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
