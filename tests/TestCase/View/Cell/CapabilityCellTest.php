<?php
namespace RolesCapabilities\Test\TestCase\View\Cell;

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use RolesCapabilities\View\Cell\CapabilityCell;
use Webmozart\Assert\Assert;

/**
 * RolesCapabilities\View\Cell\CapabilityCell Test Case
 *
 * @property \RolesCapabilities\View\Cell\CapabilityCell $Capability
 */
class CapabilityCellTest extends TestCase
{
    private $request;
    private $response;
    private $Capability;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->request = $this->getMockBuilder('Cake\Http\ServerRequest')->getMock();
        Assert::isInstanceOf($this->request, ServerRequest::class);

        $this->response = $this->getMockBuilder('Cake\Http\Response')->getMock();
        Assert::isInstanceOf($this->response, Response::class);

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
