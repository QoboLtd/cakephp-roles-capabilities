<?php

namespace Qobo\RolesCapabilities\Test\Access;

use PHPUnit\Framework\TestCase;
use Qobo\RolesCapabilities\Access\SupervisorAccess;

class SupervisorAccessTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->instance = new SupervisorAccess();
    }

    public function testHasAccess()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
