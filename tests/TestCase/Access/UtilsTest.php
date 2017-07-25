<?php

namespace RolesCapabilities\Test\TestCase\Access;

use Cake\Core\Configure;
use RolesCapabilities\Access\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTypeFull()
    {
        // This returns a static constant.  But if the value of that constant
        // changes accidentally, the reprecussions may include an incorrectly
        // configured system access control.  So we are testing for specific
        // values here to alert the programmer if this happens.
        $result = Utils::getTypeFull();
        $this->assertEquals('full', $result, "Full type capability is not 'full'");
    }

    public function testGetTypeOwner()
    {
        // This returns a static constant.  But if the value of that constant
        // changes accidentally, the reprecussions may include an incorrectly
        // configured system access control.  So we are testing for specific
        // values here to alert the programmer if this happens.
        $result = Utils::getTypeOwner();
        $this->assertEquals('owner', $result, "Owner type capability is not 'owner'");
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
        $result = Utils::getCakeControllerActions();

        foreach ($checkMethods as $method) {
            $this->assertContains($method, $result, "Cake\Controller\Controller is missing $method");
        }
    }

    /**
     * @dataProvider getCapabilityControllers
     */
    public function testGenerateCapabilityControllerName($controller, $expected)
    {
        $result = Utils::generateCapabilityControllerName($controller);
        $this->assertEquals($expected, $result);
    }

    public function getCapabilityControllers()
    {
        return [
            ['App\Controller\AppController', 'App_Controller_AppController'],
            ['Some\Plugin\Controller\AppController', 'Some_Plugin_Controller_AppController'],
            ['Foobar', 'Foobar'],
        ];
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
        $result = Utils::generateCapabilityName($controller, $action);
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
        $result = Utils::generateCapabilityLabel($controller, $action);
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
        $result = Utils::generateCapabilityDescription($controller, $action);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getControllerMethods
     */
    public function getControllerPublicMethodsTest($controller, $expected)
    {
        $result = Utils::getControllerPublicMethods($controller);
        $this->assertArrayHasKey($expected, $result);
    }

    public function getControllerMethods()
    {
        return [
            ['AppControllerdddd', 'beforeFilter444']
        ];
    }

    public function filterSkippedActionsTest()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testGetAllCapabilities()
    {
        // Info: this test fails when run on the application level, since the $result is way different.
        $this->markTestSkipped();

        Configure::write('CsvMigrations.modules.path', '');

        $result = [];
        foreach (Utils::getAllCapabilities() as $groupName => $groupCaps) {
            foreach ($groupCaps as $type => $caps) {
                foreach ($caps as $cap) {
                    $result = array_merge($result, [$cap->getName()]);
                }
            }
        }

        $name = Utils::generateCapabilityControllerName('RolesCapabilities\Test\App\Controller\ArticlesController');

        $needle = Utils::generateCapabilityName($name, 'index');
        $this->assertContains($needle, $result);

        $needle = Utils::generateCapabilityName($name, 'managePermissions');
        $this->assertContains($needle, $result);
    }
}
