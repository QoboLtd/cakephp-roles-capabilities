<?php

namespace RolesCapabilities\Test\Access;

use Cake\TestSuite\TestCase;
use RolesCapabilities\Access\CapabilitiesAccess;
use RolesCapabilities\Capability;

class CapabilitiesAccessTest extends TestCase
{
    public $fixtures = [
        'plugin.roles_capabilities.users',
        'plugin.roles_capabilities.groups',
        'plugin.roles_capabilities.groups_users',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->instance = new CapabilitiesAccess();
    }

    public function testHasAccess()
    {
        $url = [
            'plugin' => null,
            'controller' => 'Leads',
            'action' => 'index'
        ];

        $user = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'user2',
            'is_superuser' => false,
            'is_supervisor' => true,
            'reports_to' => null,
        ];

        $this->assertTrue($this->instance->hasAccess($url, $user));
    }

    public function testGetUserCapabilities()
    {
        $list = $this->instance->getUserCapabilities('00000000-0000-0000-0000-000000000002');

        $this->assertTrue(is_array($list));
        $this->assertCount(0, $list);
    }

    public function testHasParentAccess()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
