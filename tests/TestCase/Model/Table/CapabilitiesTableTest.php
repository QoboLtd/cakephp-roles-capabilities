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

    public function testGetTypeFull()
    {
        // This returns a static constant.  But if the value of that constant
        // changes accidentally, the reprecussions may include an incorrectly
        // configured system access control.  So we are testing for specific
        // values here to alert the programmer if this happens.
        $result = $this->Capabilities->getTypeFull();
        $this->assertEquals('full', $result, "Full type capability is not 'full'");
    }

    public function testGetTypeOwner()
    {
        // This returns a static constant.  But if the value of that constant
        // changes accidentally, the reprecussions may include an incorrectly
        // configured system access control.  So we are testing for specific
        // values here to alert the programmer if this happens.
        $result = $this->Capabilities->getTypeOwner();
        $this->assertEquals('owner', $result, "Owner type capability is not 'owner'");
    }

    public function testSetCurrentRequest()
    {
        $data = [
            'foo' => 'bar',
            'blah' => true,
        ];
        $this->Capabilities->setCurrentRequest($data);
        $result = $this->Capabilities->getCurrentRequest();
        $this->assertEquals($data, $result, "Setting current request is broken");
    }

    public function testGetCurrentRequest()
    {
        $data = [
            'foo' => 'bar',
            'blah' => true,
        ];
        $this->Capabilities->setCurrentRequest($data);
        $result = $this->Capabilities->getCurrentRequest('foo');
        $this->assertEquals('bar', $result, "Getting keys of current request is broken");
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

    public function testSetUserActionCapability()
    {
        $this->Capabilities->setUserActionCapability('plugin_x', 'controller_x', 'action_x', 'type_x', new Capability('cap_x'));
        $this->Capabilities->setUserActionCapability('plugin_x', 'controller_x', 'action_x', 'type_x', new Capability('cap_y'));
        $result = $this->Capabilities->getUserActionCapabilities();
        $this->assertTrue(isset($result['plugin_x']), "Setting user action capabilities for plugin is broken");
        $this->assertTrue(isset($result['plugin_x']['controller_x']), "Setting user action capabilities for controller is broken");
        $this->assertTrue(isset($result['plugin_x']['controller_x']['action_x']), "Setting user action capabilities for action is broken");
        $this->assertTrue(isset($result['plugin_x']['controller_x']['action_x']['type_x']), "Setting user action capabilities for type is broken");
        $this->assertEquals(2, count($result['plugin_x']['controller_x']['action_x']['type_x']), "Setting user action capabilities is broken by count");
    }

    public function testGetCakeControllerActions()
    {
        /* Actions in the CakePHP's Controller class can vary,
         * depending on the version of CakePHP, installed plugins,
         * etc.  Here are a few methods to check for.  These are
         * very unlikely to be there.
         */
        $checkMethods = [
            '__construct',
            'beforeFilter',
            'initialize',
            'invokeAction',
            'loadComponent',
            'redirect',
            'render',
            'requestAction',
            'set',
        ];
        $result = $this->Capabilities->getCakeControllerActions();

        foreach ($checkMethods as $method) {
            $this->assertContains($method, $result, "Cake\Controller\Controller is missing $method");
        }
    }

    public function getCapabilityControllers()
    {
        return [
            ['App\Controller\AppController', 'App_Controller_AppController'],
            ['Some\Plugin\Controller\AppController', 'Some_Plugin_Controller_AppController'],
            ['Foobar', 'Foobar'],
        ];
    }

    /**
     * @dataProvider getCapabilityControllers
     */
    public function testGenerateCapabilityControllerName($controller, $expected)
    {
        $result = $this->Capabilities->generateCapabilityControllerName($controller);
        $this->assertEquals($expected, $result);
    }

    public function getCapabilityNames()
    {
        return [
            ['AppController', 'index', 'cap__AppController__index'],
            ['SomeController', 'doSomething', 'cap__SomeController__doSomething'],
            ['SomeController', 'do_something', 'cap__SomeController__do_something'],
        ];
    }

    /**
     * @dataProvider getCapabilityNames
     */
    public function testGenerateCapabilityName($controller, $action, $expected)
    {
        $result = $this->Capabilities->generateCapabilityName($controller, $action);
        $this->assertEquals($expected, $result);
    }

    public function getCapabilityLabels()
    {
        return [
            ['AppController', 'index', 'Cap AppController index'],
            ['SomeController', 'doSomething', 'Cap SomeController doSomething'],
            ['SomeController', 'do_something', 'Cap SomeController do_something'],
        ];
    }

    /**
     * @dataProvider getCapabilityLabels
     */
    public function testGenerateCapabilityLabel($controller, $action, $expected)
    {
        $result = $this->Capabilities->generateCapabilityLabel($controller, $action);
        $this->assertEquals($expected, $result);
    }

    public function getCapabilityDescriptions()
    {
        return [
            ['AppController', 'index', 'Allow index'],
            ['SomeController', 'doSomething', 'Allow doSomething'],
            ['SomeController', 'do_something', 'Allow do_something'],
        ];
    }

    /**
     * @dataProvider getCapabilityDescriptions
     */
    public function testGenerateCapabilityDescription($controller, $action, $expected)
    {
        $result = $this->Capabilities->generateCapabilityDescription($controller, $action);
        $this->assertEquals($expected, $result);
    }
}
