<?php
namespace RolesCapabilities\Test\TestCase\View\Cell;

use Cake\TestSuite\TestCase;
use RolesCapabilities\View\Cell\CapabilityCell;

/**
 * RolesCapabilities\View\Cell\CapabilityCell Test Case
 *
 * @property \RolesCapabilities\View\Cell\CapabilityCell $Capability
 */
class CapabilityCellTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->request = $this->getMockBuilder('Cake\Http\ServerRequest')->getMock();
        $this->response = $this->getMockBuilder('Cake\Http\Response')->getMock();
        $this->Capability = new CapabilityCell($this->request, $this->response);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Capability);

        parent::tearDown();
    }

    /**
     * Test display method
     *
     * @return void
     */
    public function testDisplay(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
