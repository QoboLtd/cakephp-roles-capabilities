<?php
namespace RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Model\Table\PersonalPermissionsTable;

/**
 * RolesCapabilities\Model\Table\PersonalPermissionsTable Test Case
 */
class PersonalPermissionsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \RolesCapabilities\Model\Table\PersonalPermissionsTable
     */
    public $PersonalPermissions;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.roles_capabilities.personal_permissions',
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
        $config = TableRegistry::exists('PersonalPermissions') ? [] : ['className' => 'RolesCapabilities\Model\Table\PersonalPermissionsTable'];
        $this->PersonalPermissions = TableRegistry::get('PersonalPermissions', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->PersonalPermissions);

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
}
