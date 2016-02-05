<?php
namespace RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Model\Table\CapabilitiesTable;

/**
 * RolesCapabilities\Model\Table\CapabilitiesTable Test Case
 */
class CapabilitiesTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.roles_capabilities.capabilities',
        'plugin.roles_capabilities.roles',
        'plugin.roles_capabilities.capabilities_roles'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Capabilities') ? [] : ['className' => 'RolesCapabilities\Model\Table\CapabilitiesTable'];
        $this->Capabilities = TableRegistry::get('Capabilities', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Capabilities);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}