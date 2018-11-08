<?php

namespace RolesCapabilities\Test\Access;

use PHPUnit\Framework\TestCase;
use RolesCapabilities\Access\SupervisorAccess;

/**
 * @property \RolesCapabilities\Access\SupervisorAccess $instance
 */
class SupervisorAccessTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->instance = new SupervisorAccess();
    }

    public function testHasAccess(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
