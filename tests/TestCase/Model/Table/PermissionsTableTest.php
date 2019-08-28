<?php
namespace RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Model\Table\PermissionsTable;

/**
 * RolesCapabilities\Model\Table\PermissionsTable Test Case
 */
class PermissionsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \RolesCapabilities\Model\Table\PermissionsTable
     */
    public $Permissions;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.roles_capabilities.permissions',
        'plugin.roles_capabilities.users'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Permissions') ? [] : ['className' => 'RolesCapabilities\Model\Table\PermissionsTable'];
        /**
         * @var \RolesCapabilities\Model\Table\PermissionsTable $table
         */
        $table = TableRegistry::get('Permissions', $config);
        $this->Permissions = $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Permissions);

        parent::tearDown();
    }

    public function testSave(): void
    {
        $data = [
            'creator' => '00000000-0000-0000-0000-000000000001',
            'type' => 'view',
            'owner_model' => 'Users',
            'owner_foreign_key' => '00000000-0000-0000-0000-000000000003',
            'model' => 'Articles',
            'foreign_key' => '00000000-0000-0000-0000-000000000001'
        ];

        $permission = $this->Permissions->newEntity($data);

        $this->Permissions->save($permission);

        $this->assertNotEmpty($permission->get('id'));
        $this->assertSame($data['creator'], $permission->get('creator'));
        $this->assertSame($data['type'], $permission->get('type'));
        $this->assertSame($data['owner_model'], $permission->get('owner_model'));
        $this->assertSame($data['owner_foreign_key'], $permission->get('owner_foreign_key'));
        $this->assertSame($data['model'], $permission->get('model'));
        $this->assertSame($data['foreign_key'], $permission->get('foreign_key'));
        $this->assertSame(null, $permission->get('expired'));
    }

    public function testfetchUserViewPermission() : void
    {
        $permission = $this->Permissions->fetchUserViewPermission(
            'Leads',
            'c4bd0658-f0d8-482b-bf02-4ffe45f18bdf',
            '00000000-0000-0000-0000-000000000003'
        );

        $this->assertSame('00000000-0000-0000-0000-000000000001', $permission->get('id'));
    }
}
