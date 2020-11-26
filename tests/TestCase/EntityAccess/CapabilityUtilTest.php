<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\EntityAccess;

use Cake\TestSuite\TestCase;
use RolesCapabilities\EntityAccess\CapabilitiesUtil;

class CapabilityUtilTest extends TestCase
{
    public function testListTables(): void
    {
        $tables = CapabilitiesUtil::getTables();

        $this->assertContains('CakeDC/Users.Users', $tables, 'Table not found');
        $this->assertContains('Groups.Groups', $tables, 'Table not found');
        $this->assertContains('RolesCapabilities.Roles', $tables, 'Table not found');
    }
}
