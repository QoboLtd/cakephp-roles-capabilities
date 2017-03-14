<?php
namespace RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Capability;
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

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testSetCurrentUser()
    {
        $data = [
            'foo' => 'bar',
            'blah' => true,
        ];
        $this->Capabilities->setCurrentUser($data);
        $result = $this->Capabilities->getCurrentUser();
        $this->assertEquals($data, $result, "Setting current user is broken");
    }

    public function testGetCurrentUser()
    {
        $data = [
            'foo' => 'bar',
            'blah' => true,
        ];
        $this->Capabilities->setCurrentUser($data);
        $result = $this->Capabilities->getCurrentUser('foo');
        $this->assertEquals('bar', $result, "Getting keys of current user is broken");
    }
}
