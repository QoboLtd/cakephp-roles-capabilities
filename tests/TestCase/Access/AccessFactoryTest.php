<?php

namespace RolesCapabilities\Test\TestCase;

use RolesCapabilities\Access\AccessFactory;

class AccessFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorRules()
    {
        $rules = [
            'SuperUser', 'Authorize', 'Capabilities'
        ];
        $af = new AccessFactory($rules);
        $this->assertArraySubset($af->getChecRules(), $rules);
    }
}
