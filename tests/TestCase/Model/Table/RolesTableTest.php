<?php
namespace RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Model\Table\RolesTable;

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
        $this->assertEquals($this->Roles->table(), 'roles', 'Table name');
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
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testPrepareCapabilities()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
