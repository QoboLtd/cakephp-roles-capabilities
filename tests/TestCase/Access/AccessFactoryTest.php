<?php

namespace RolesCapabilities\Test\TestCase\Access;

use RolesCapabilities\Access\AccessFactory;

class AccessFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorRules()
    {
        $rules = [
            'MyRule1', 'MyRule2'
        ];
        $af = new AccessFactory($rules);
        $this->assertArraySubset($af->getCheckRules(), $rules);
    }

    public function testSkipAction()
    {
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $url = [
           'plugin' => 'Blah',
           'controller' => 'Foo',
           'action' => 'login'
        ];

        $af = new AccessFactory();
        $result = $af->hasAccess($url, $user);
        $this->assertTrue($result);
    }

    public function testIsAuthenticated()
    {
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $url = [
           'plugin' => 'Blah',
           'controller' => 'Foo',
           'action' => 'view'
        ];

        $af = new AccessFactory();
        $result = $af->hasAccess($url, $user);
        $this->assertTrue($result);

        $result = $af->hasAccess($url, []);
        $this->assertFalse($result);
    }

    public function testHasAccessSuperUser()
    {
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'is_superuser' => true,
        ];

        $url = [
           'plugin' => 'Blah',
           'controller' => 'Foo',
           'action' => 'view'
        ];

        $af = new AccessFactory();
        $result = $af->hasAccess($url, $user);
        $this->assertTrue($result);
    }

    public function testUnknownRule()
    {
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];

        $url = [
           'plugin' => 'Blah',
           'controller' => 'Foo',
           'action' => 'view'
        ];

        $af = new AccessFactory(['blah', 'bar', 'foo']);
        $this->expectException(\InvalidArgumentException::class);
        $result = $af->hasAccess($url, $user);
    }
}
