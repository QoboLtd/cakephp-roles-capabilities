<?php

namespace RolesCapabilities\Test\TestCase;

use RolesCapabilities\Access\AccessFactory;

class AccessFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorRules()
    {
        $rules = [
            'MyRule1', 'MyRule2'
        ];
        $af = new AccessFactory($rules);
        $this->assertArraySubset($af->getChecRules(), $rules);
    }

    public function testHasAccessAuthorize()
    {
        $user = []; 
        $url = [
           'plugin'     => 'Blah',
           'controller' => 'Foo',
           'action'     => 'view' 
        ];

        $af = new AccessFactory();
        $result = $af->hasAccess($url, $user);
        $this->assertTrue($result);
        
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];        
        $result = $af->hasAccess($url, $user);
        //$this->assertNotTrue($result);

    }

    public function testHasAccessSuperUser()
    {
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'is_superuser' => true,
        ];
        
        $url = [
           'plugin'     => 'Blah',
           'controller' => 'Foo',
           'action'     => 'view' 
        ];
        
        $af = new AccessFactory();
        $result = $af->hasAccess($url, $user);
        $this->assertTrue($result);
    }
}
