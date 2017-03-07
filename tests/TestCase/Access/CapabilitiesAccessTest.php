<?php

namespace RolesCapabilities\Test\Access;

use RolesCapabilities\Access\CapabilitiesAccess;
use RolesCapabilities\Capability;

class CapabilitiesAccessTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->CapabilitiesAccess = new CapabilitiesAccess();
    }

    public function testSetUserActionCapability()
    {
        $this->CapabilitiesAccess->setUserActionCapability('plugin_x', 'controller_x', 'action_x', 'type_x', new Capability('cap_x'));
        $this->CapabilitiesAccess->setUserActionCapability('plugin_x', 'controller_x', 'action_x', 'type_x', new Capability('cap_y'));
        $result = $this->CapabilitiesAccess->getUserActionCapabilities();
        $this->assertTrue(isset($result['plugin_x']), "Setting user action capabilities for plugin is broken");
        $this->assertTrue(isset($result['plugin_x']['controller_x']), "Setting user action capabilities for controller is broken");
        $this->assertTrue(isset($result['plugin_x']['controller_x']['action_x']), "Setting user action capabilities for action is broken");
        $this->assertTrue(isset($result['plugin_x']['controller_x']['action_x']['type_x']), "Setting user action capabilities for type is broken");
        $this->assertEquals(2, count($result['plugin_x']['controller_x']['action_x']['type_x']), "Setting user action capabilities is broken by count");

    }
}
