<?php

namespace RolesCapabilities\Test\TestCase\Access;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RolesCapabilities\Access\AccessFactory;

class AccessFactoryTest extends TestCase
{
    public function testConstructorRules(): void
    {
        $rules = [
            'MyRule1', 'MyRule2'
        ];
        $af = new AccessFactory($rules);
        $this->assertArraySubset($af->getCheckRules(), $rules);
    }

    public function testSkipAction(): void
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

    public function testIsAuthenticated(): void
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

    public function testHasAccessSuperUser(): void
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

    public function testUnknownRule(): void
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
        $this->expectException(InvalidArgumentException::class);
        $result = $af->hasAccess($url, $user);
    }
}
