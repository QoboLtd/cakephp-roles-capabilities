<?php

namespace RolesCapabilities\Test\Access;

use RolesCapabilities\Access\CapabilitiesAccess;
use RolesCapabilities\Capability;

class CapabilitiesAccessTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->capabilitiesAccess = new CapabilitiesAccess();
    }

    public function testHasAccess()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testGetUserCapabilities()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
