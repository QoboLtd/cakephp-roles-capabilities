<?php

namespace RolesCapabilities\Test\Access;

use PHPUnit\Framework\TestCase;
use RolesCapabilities\Access\SuperUserAccess;

/**
 * @property \RolesCapabilities\Access\SuperUserAccess $instance
 */
class SuperUserAccessTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->instance = new SuperUserAccess();
    }

    public function testHasAccess(): void
    {
        $user = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'is_superuser' => true,
        ];

        $url = [
           'plugin' => 'Blah',
           'controller' => 'Foo',
           'action' => 'view',
        ];

        $this->assertTrue($this->instance->hasAccess($url, $user), 'User has superuser flag');

        $user['is_superuser'] = false;
        $this->assertFalse($this->instance->hasAccess($url, $user), 'User does not have superuser flag');
    }
}
