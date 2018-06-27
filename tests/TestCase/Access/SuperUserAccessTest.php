<?php

namespace RolesCapabilities\Test\Access;

use RolesCapabilities\Access\SuperUserAccess;

class SuperUserAccessTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->instance = new SuperUserAccess();
    }

    public function testHasAccess()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
