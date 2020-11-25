<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\EntityAccess;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\EntityAccess\CapabilitiesUtil;

class CapabilityUtilTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.RolesCapabilities.ExtendedCapabilities',
        'plugin.RolesCapabilities.Groups',
        'plugin.RolesCapabilities.GroupsRoles',
        'plugin.RolesCapabilities.GroupsUsers',
        'plugin.RolesCapabilities.Permissions',
        'plugin.RolesCapabilities.Roles',
        'plugin.RolesCapabilities.Users',
    ];

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        TableRegistry::clear();
        parent::tearDown();
    }

    public function testListTables(): void
    {
        $tables = CapabilitiesUtil::getTables();

        $this->assertContains('CakeDC/Users.Users', $tables, 'Table not found');
        $this->assertContains('Groups.Groups', $tables, 'Table not found');
        $this->assertContains('RolesCapabilities.Roles', $tables, 'Table not found');
    }
}
