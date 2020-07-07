<?php

namespace RolesCapabilities\Test\TestCase\Access;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Qobo\Utils\TestSuite\Utility;
use RolesCapabilities\Access\Utils;

class UtilsTest extends TestCase
{
    public $fixtures = [
        'plugin.RolesCapabilities.Users',
        'plugin.RolesCapabilities.Groups',
        'plugin.Groups.GroupsUsers',
    ];

    public function testGetControllerFullName(): void
    {
        // Empty URL
        $result = Utils::getControllerFullName([]);
        $this->assertTrue(is_null($result), "getControllerFullName() returns a non-null result for empty controller");

        // Non-existing controller
        $result = Utils::getControllerFullName(['controller' => 'NonExistingController']);
        $this->assertTrue(is_null($result), "getControllerFullName() returns a non-null result for non-existing controller");

        // Existing controller
        $result = Utils::getControllerFullName(['controller' => 'App']);
        $this->assertTrue(is_string($result), "getControllerFullName() returns a non-string result for existing controller");
        $this->assertEquals('RolesCapabilities\\Test\\App\Controller\\AppController', $result, "getControllerFullName() returns a wrong name for existing controller");
    }

    public function testGetTypeFull(): void
    {
        // This returns a static constant.  But if the value of that constant
        // changes accidentally, the reprecussions may include an incorrectly
        // configured system access control.  So we are testing for specific
        // values here to alert the programmer if this happens.
        $result = Utils::getTypeFull();
        $this->assertEquals('full', $result, "Full type capability is not 'full'");
    }

    public function testGetTypeOwner(): void
    {
        // This returns a static constant.  But if the value of that constant
        // changes accidentally, the reprecussions may include an incorrectly
        // configured system access control.  So we are testing for specific
        // values here to alert the programmer if this happens.
        $result = Utils::getTypeOwner();
        $this->assertEquals('owner', $result, "Owner type capability is not 'owner'");
    }

    public function testGetTypeBelongs(): void
    {
        $result = Utils::getTypeBelongs();
        $this->assertEquals('belongs', $result, "Belongs to capability is not 'belongs_to'");
    }

    public function testGetTypeParent(): void
    {
        $result = Utils::getTypeParent();
        $this->assertEquals('parent', $result, "Parent capability is not 'parent'");
    }

    public function testGetCakeControllerActions(): void
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
    public function testGenerateCapabilityControllerName(string $controller, string $expected): void
    {
        $result = Utils::generateCapabilityControllerName($controller);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return mixed[]
     */
    public function getCapabilityControllers(): array
    {
        return [
            ['App\Controller\AppController', 'App_Controller_AppController'],
            ['Some\Plugin\Controller\AppController', 'Some_Plugin_Controller_AppController'],
            ['Foobar', 'Foobar'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getCapabilityNames(): array
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
    public function testGenerateCapabilityName(string $controller, string $action, string $expected): void
    {
        $result = Utils::generateCapabilityName($controller, $action);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return mixed[]
     */
    public function getCapabilityLabels(): array
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
    public function testGenerateCapabilityLabel(string $controller, string $action, string $expected): void
    {
        $result = Utils::generateCapabilityLabel($controller, $action);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return mixed[]
     */
    public function getCapabilityDescriptions(): array
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
    public function testGenerateCapabilityDescription(string $controller, string $action, string $expected): void
    {
        $result = Utils::generateCapabilityDescription($controller, $action);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getHumanizeActions
     */
    public function testHumanizeActionName(string $action, string $expected): void
    {
        $actual = Utils::humanizeActionName($action);
        $this->assertTrue(is_string($actual), "humanizeActionName() returned a non-string result");
        $this->assertEquals($expected, $actual, "humanizeActionName() is broken");
    }

    /**
     * @return mixed[]
     */
    public function getHumanizeActions(): array
    {
        return [
            ['doNoEvil', 'do no evil'],
            ['index', 'list'],
            ['info', 'view info'],
            ['changelog', 'view changelog'],
        ];
    }

    public function testGetControllerPublicMethodsBadController(): void
    {
        $result = Utils::getControllerPublicMethods('Foobar');
        $this->assertTrue(is_array($result), "getControllerPublicMethods() returned a non-array result");
        $this->assertTrue(empty($result), "getControllerMethods() returned a non-empty result for non-existing controller");
    }

    /**
     * @dataProvider getControllerMethods
     */
    public function testGetControllerPublicMethods(string $controller, string $expected): void
    {
        $result = Utils::getControllerPublicMethods($controller);
        $this->assertTrue(is_array($result), "getControllerPublicMethods() returned a non-array result");
        $this->assertTrue(in_array($expected, $result));
    }

    /**
     * @return mixed[]
     */
    public function getControllerMethods(): array
    {
        return [
            [__CLASS__, __FUNCTION__],
        ];
    }

    public function testFilterSkippedActions(): void
    {
        $result = Utils::filterSkippedActions('Foobar', ['blah']);
        $this->assertTrue(is_array($result), "filterSkippedActions() returned a non-array result");
    }

    public function testGetActions(): void
    {
        $result = Utils::getActions('Foobar', ['blah']);
        $this->assertTrue(is_array($result), "getActions() returned a non-array result");
    }

    public function testGetAllCapabilities(): void
    {
        Configure::write('CsvMigrations.modules.path', '');

        $result = Utils::getAllCapabilities();
        $this->assertTrue(is_array($result), "getAllCapabilities() returned a non-array result");
        $this->assertFalse(empty($result), "getAllCapabilities() returned an empty result");
    }

    public function testFetchUserCapabilities(): void
    {
        $result = Utils::fetchUserCapabilities('00000000-0000-0000-0000-000000000001');
        $this->assertTrue(is_array($result), "fetchUserCapabilities() returned a non-array result");
        $this->assertTrue(empty($result), "fetchUserCapabilities() returned a non-empty result");
    }

    public function testGetCapabilities(): void
    {
        $result = Utils::getCapabilities('');
        $this->assertTrue(is_array($result), "getCapabilities() returned a non-array result for empty controller");
        $this->assertTrue(empty($result), "getCapabilities() returned a non-empty result for empty controller");
    }

    public function testGetReportToUsers(): void
    {
        $list = Utils::getReportToUsers('00000000-0000-0000-0000-000000000002');
        $this->assertTrue(is_array($list), 'Return an aray');
        $this->assertCount(2, $list, 'Count is 2');

        $list = Utils::getReportToUsers('00000000-0000-0000-0000-000000000001');
        $this->assertTrue(is_array($list), 'Return an array');
        $this->assertCount(0, $list, 'Count is 0');
    }

    /**
     * Test getEntityFromUrl method
     */
    public function testGetEntityFromUrl(): void
    {
        $url = [
            'pass' => ["00000000-0000-0000-0000-000000000001"],
            'plugin' => null,
            'controller' => 'Users',
        ];

        $data = Utility::callStaticPrivateMethod('\RolesCapabilities\Access\Utils', 'getEntityFromUrl', [$url]);

        $this->assertInstanceOf('\Cake\ORM\Entity', $data);
    }

    /**
     * Test getEntityFromUrl method without the pass parameter
     */
    public function testGetEntityFromUrlWithoutPassParameter(): void
    {
        $url = [
            '0' => "00000000-0000-0000-0000-000000000001",
            'plugin' => null,
            'controller' => 'Users',
        ];

        $data = Utility::callStaticPrivateMethod('\RolesCapabilities\Access\Utils', 'getEntityFromUrl', [$url]);

        $this->assertInstanceOf('\Cake\ORM\Entity', $data);
    }

    /**
     * Test getEntityFromUrl method with the plugin set
     */
    public function testGetEntityFromUrlWithPluginSetParameter(): void
    {
        $url = [
            '0' => "00000000-0000-0000-0000-000000000001",
            'plugin' => 'Users',
            'controller' => 'Users',
        ];

        $data = Utility::callStaticPrivateMethod('\RolesCapabilities\Access\Utils', 'getEntityFromUrl', [$url]);

        $this->assertInstanceOf('\Cake\ORM\Entity', $data);
    }

    /**
     * Test hasAccessInCapabilities method the user has the capabilities
     */
    public function testHasAccessInCapabilities(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hasAccessInCapabilities method the user doesnt has any capabilities
     */
    public function testHasAccessInCapabilitiesWithNoCapabilities(): void
    {
        $bool = Utils::hasAccessInCapabilities("view", "00000000-0000-0000-0000-000000000001");
        $this->assertFalse($bool);
    }

    /**
     * Test getUserGroups method with groups
     */
    public function testGetUserGroups(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getUserGroups method when there are no groups
     */
    public function testGetUserGroupsNoGroup(): void
    {
        $url = ['id' => "00000000-0000-0000-0000-000000000001"];

        $data = Utility::callStaticPrivateMethod('\RolesCapabilities\Access\Utils', 'getUserGroups', [$url]);
        $this->assertEquals([], $data);
    }

    /**
     * Test hasTypeAccessBelongs with capabilities
     */
    public function testHasTypeAccessBelongs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hasTypeAccessBelongs when there are no capabilities
     */
    public function testHasTypeAccessBelongsWithoutCapabilities(): void
    {
        $parameters = [
            'capabilities' => [],
            'user' => ['id' => '00000000-0000-0000-0000-000000000001'],
            'url' => [
                'pass' => ["00000000-0000-0000-0000-000000000001"],
                'plugin' => null,
                'controller' => 'Users',
            ],
        ];

        $bool = Utility::callStaticPrivateMethod('\RolesCapabilities\Access\Utils', 'hasTypeAccessBelongs', $parameters);
        $this->assertFalse($bool);
    }

    /**
     * Test hasTypeAccessOwner with capabilities
     */
    public function testHasTypeAccessOwner(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hasTypeAccessOwner when there are no capabilities
     */
    public function testHasTypeAccessOwnerWithoutCapabilities(): void
    {
        $parameters = [
            'capabilities' => [],
            'user' => ['id' => '00000000-0000-0000-0000-000000000001'],
            'url' => [
                'pass' => ["00000000-0000-0000-0000-000000000001"],
                'plugin' => null,
                'controller' => 'Users',
            ],
        ];

        $bool = Utility::callStaticPrivateMethod('\RolesCapabilities\Access\Utils', 'hasTypeAccessOwner', $parameters);
        $this->assertFalse($bool);
    }

    /**
     * Test hasTypeAccessFull with capabilities
     */
    public function testHasTypeAccessFull(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hasTypeAccessFull when there are no capabilities
     */
    public function testHasTypeAccessFullWithoutCapabilities(): void
    {
        $parameters = [
            'capabilities' => [],
            'user' => ['id' => '00000000-0000-0000-0000-000000000001'],
            'url' => [
                'pass' => ["00000000-0000-0000-0000-000000000001"],
                'plugin' => null,
                'controller' => 'Users',
            ],
        ];

        $bool = Utility::callStaticPrivateMethod('\RolesCapabilities\Access\Utils', 'hasTypeAccessFull', $parameters);
        $this->assertFalse($bool);
    }

    /**
     * Test hasTypeAccess with capabilities
     */
    public function testHasTypeAccessHasCapabilitiesButNoAccess(): void
    {
        $url = [
            'pass' => ["00000000-0000-0000-0000-000000000001"],
            'plugin' => null,
            'controller' => 'Users',
        ];

        $bool = Utils::hasTypeAccess(Utils::getTypeFull(), ['full' => []], ['id' => '00000000-0000-0000-0000-000000000001'], $url);
        $this->assertFalse($bool);
    }

    /**
     * Test hasTypeAccess when there are no capabilities
     */
    public function testHasTypeAccessWithoutCapabilities(): void
    {
        $url = [
            'pass' => ["00000000-0000-0000-0000-000000000001"],
            'plugin' => null,
            'controller' => 'Users',
        ];

        $bool = Utils::hasTypeAccess(Utils::getTypeFull(), [], ['id' => '00000000-0000-0000-0000-000000000001'], $url);
        $this->assertFalse($bool);
    }

    /**
     * Test hasTypeAccess when wrong type
     */
    public function testHasTypeAccessWrongType(): void
    {
        $url = [
            'pass' => ["00000000-0000-0000-0000-000000000001"],
            'plugin' => null,
            'controller' => 'Users',
        ];

        $bool = Utils::hasTypeAccess('half', ['half' => []], ['id' => '00000000-0000-0000-0000-000000000001'], $url);
        $this->assertFalse($bool);
    }
}
