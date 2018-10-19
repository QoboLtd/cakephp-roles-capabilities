<?php
namespace Qobo\RolesCapabilities\Test\TestCase\View\Cell;

use Cake\TestSuite\TestCase;
use Qobo\RolesCapabilities\View\Cell\CapabilityCell;

/**
 * RolesCapabilities\View\Cell\CapabilityCell Test Case
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
        $this->request = $this->getMockBuilder('Cake\Network\Request')->getMock();
        $this->response = $this->getMockBuilder('Cake\Network\Response')->getMock();
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
    public function testDisplay()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
