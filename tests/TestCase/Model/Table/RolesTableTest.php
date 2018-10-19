<?php
namespace Qobo\RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\RolesCapabilities\Model\Table\RolesTable;

/**
 * RolesCapabilities\Model\Table\RolesTable Test Case
 */
class RolesTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.qobo/roles_capabilities.roles',
        'plugin.qobo/roles_capabilities.capabilities',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Roles') ? [] : ['className' => 'Qobo\RolesCapabilities\Model\Table\RolesTable'];
        $this->Roles = TableRegistry::get('Roles', $config);
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
    public function testInitialize()
    {
        $this->assertEquals($this->Roles->table(), 'qobo_roles', 'Table name');
        $this->assertEquals($this->Roles->displayField(), 'name', 'Display field');
        $this->assertEquals($this->Roles->primaryKey(), 'id', 'Primary key');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $role = $this->Roles->newEntity([
            'name' => 'test',
        ]);
        $this->assertCount(0, $role->errors(), 'No errors');
        $role = $this->Roles->newEntity([
            'created' => date('Y-m-d H:i:s'),
        ]);
        $this->assertArraySubset([
            'name' => [
                '_required' => 'This field is required'
            ]
        ], $role->errors(), 'Missing required property *name* error');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
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
        ], $role2->errors(), 'Non unique role name');

        $role1 = $this->Roles->patchEntity($role1, ['description' => 'New description']);
        $this->Roles->save($role1);
        $this->assertArraySubset([], $role1->errors(), 'Non editable entity');
    }

    public function testPrepareCapabilities()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
