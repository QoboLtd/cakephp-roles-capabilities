<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\EntityAccess;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\EntityAccess\CapabilitiesUtil;
use RolesCapabilities\EntityAccess\Operation;

class CapabilityUtilTest extends TestCase
{
    /**
     * @var ?\Cake\ORM\Table
     */
    private $Users;

    public function setUp(): void
    {
        parent::setUp();

        $this->Users = TableRegistry::getTableLocator()->get('RolesCapabilities.Users');
        $this->Users->addBehavior('RolesCapabilities.Authorized', [
            'associations' => [
                'Self' => [ 'association' => 'field', 'field' => 'id'],
            ],
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'Self'],
            ],
        ]);
    }

    public function tearDown(): void
    {
        TableRegistry::clear();
        $this->Users = null;
        parent::tearDown();
    }

    public function testListTables(): void
    {
        $tables = CapabilitiesUtil::getTables();

        $this->assertContains('CakeDC/Users.Users', $tables, 'Table not found');
        $this->assertContains('Groups.Groups', $tables, 'Table not found');
        $this->assertContains('RolesCapabilities.Roles', $tables, 'Table not found');
    }

    public function testGetStaticCapabilities(): void
    {
        $capabilities = CapabilitiesUtil::getModelStaticCapabities($this->Users);

        $this->assertCount(1, $capabilities, 'Table does not have exactly 1 capability');
    }

    public function testGetStaticAssociations(): void
    {
        $associations = CapabilitiesUtil::getModelCapabilityAssociations($this->Users);

        $this->assertArrayHasKey('Self', $associations);
    }

    public function testGetAllCapabilities(): void
    {
        $all = CapabilitiesUtil::getAllCapabilities();

        $this->assertArrayHasKey('RolesCapabilities.Roles', $all, 'All capabilities');
    }
}
