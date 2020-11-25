<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Capability;
use RolesCapabilities\Model\Table\ExtendedCapabilitiesTable;

/**
 * RolesCapabilities\Model\Table\CapabilitiesTable Test Case
 *
 * @property ExtendedCapabilitiesTable $ExtendedCapabilities
 */
class ExtendedCapabilitiesTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.RolesCapabilities.ExtendedCapabilities',
        'plugin.RolesCapabilities.Roles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('ExtendedCapabilities') ? [] : ['className' => 'RolesCapabilities\Model\Table\ExtendedCapabilitiesTable'];
        /**
         * @var \RolesCapabilities\Model\Table\ExtendedCapabilitiesTable $table
         */
        $table = TableRegistry::get('ExtendedCapabilities', $config);
        $this->ExtendedCapabilities = $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ExtendedCapabilities);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $entity = $this->ExtendedCapabilities->newEntity(
            [
                'role_id' => 'INVALID-ROLE-ID',
            ]
        );

        $saved = $this->ExtendedCapabilities->save($entity);

        $this->assertEquals($saved, false, 'Invalid role id accepted');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testSave(): void
    {
        $entity = $this->ExtendedCapabilities->newEntity(
            [
                'role_id' => '00000000-0000-0000-0000-000000000002',
                'resource' => 'TestResource',
                'association' => 'All',
                'operation' => 'view',
            ]
        );

        $capability = $this->ExtendedCapabilities->save($entity);

        $this->assertNotEmpty($capability, 'Capability not saved');
    }
}
