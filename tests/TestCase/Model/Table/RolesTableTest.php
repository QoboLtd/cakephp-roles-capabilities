<?php
namespace RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Model\Table\RolesTable;

/**
 * RolesCapabilities\Model\Table\RolesTable Test Case
 *
 * @property \RolesCapabilities\Model\Table\RolesTable $Roles
 */
class RolesTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.roles_capabilities.roles',
        'plugin.roles_capabilities.capabilities',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Roles') ? [] : ['className' => 'RolesCapabilities\Model\Table\RolesTable'];
        /**
         * @var \RolesCapabilities\Model\Table\RolesTable $table
         */
        $table = TableRegistry::get('Roles', $config);
        $this->Roles = $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Roles);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertEquals($this->Roles->getTable(), 'qobo_roles', 'Table name');
        $this->assertEquals($this->Roles->getDisplayField(), 'name', 'Display field');
        $this->assertEquals($this->Roles->getPrimaryKey(), 'id', 'Primary key');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $role = $this->Roles->newEntity([
            'name' => 'test',
        ]);
        $this->assertCount(0, $role->getErrors(), 'No errors');
        $role = $this->Roles->newEntity([
            'created' => date('Y-m-d H:i:s'),
        ]);
        $this->assertArraySubset([
            'name' => [
                '_required' => 'This field is required'
            ]
        ], $role->getErrors(), true, 'Missing required property *name* error');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $role1 = $this->Roles->newEntity([
            'name' => 'test',
            'description' => 'Test description',
            'deny_edit' => false,
            'deny_delete' => false,
        ]);
        $this->Roles->save($role1);

        $role2 = $this->Roles->newEntity([
            'name' => 'test'
        ]);
        $this->assertArraySubset([
            'name' => [
                'unique' => 'The provided value is invalid'
            ]
        ], $role2->getErrors(), true, 'Non unique role name');

        $role1 = $this->Roles->patchEntity($role1, ['description' => 'New description']);
        $this->Roles->save($role1);
        $this->assertArraySubset([], $role1->getErrors(), true, 'Non editable entity');
    }

    public function testPrepareCapabilities(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
