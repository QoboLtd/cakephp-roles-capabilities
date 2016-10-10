<?php
namespace RolesCapabilities\Test\TestCase;

use RolesCapabilities\Capability;

class CapabilityTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorOptions()
    {
        $options = [
            'label' => 'Foobar',
            'description' => 'Foobar Capability',
            'field' => 'some_field',
        ];
        $cap = new Capability('Blah', $options);
        $this->assertEquals('Foobar', $cap->getLabel(), "Constructor option for label is broken");
        $this->assertEquals('Foobar Capability', $cap->getDescription(), "Constructor option for description is broken");
        $this->assertEquals('some_field', $cap->getField(), "Constructor option for field is broken");
    }

    public function testToString()
    {
        $cap = new Capability('Foobar');
        $result = (string)$cap;
        $this->assertEquals('Foobar', $result, "Capability casting to string is broken");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNameException()
    {
        $cap = new Capability('foobar');
        $cap->setName('');
    }

    public function testSetName()
    {
        $cap = new Capability('Blah');
        $cap->setName('Foobar');
        $this->assertEquals('Foobar', $cap->getName(), "Capability name setting is broken");
    }

    public function testSetlabel()
    {
        $cap = new Capability('Awesome_Capability');
        $cap->setLabel('My Capability');
        $this->assertEquals('My Capability', $cap->getLabel(), "Capability label setting is broken");

        $cap->setLabel('');
        $this->assertEquals('Awesome Capability', $cap->getLabel(), "Capability label fallback is broken");
    }

    public function testSetDescription()
    {
        $cap = new Capability('Foobar');
        $cap->setDescription('Blah');
        $this->assertEquals('Blah', $cap->getDescription(), "Capability description setting is broken");
    }

    public function testSetField()
    {
        $cap = new Capability('Foobar');
        $cap->setField('some_field');
        $this->assertEquals('some_field', $cap->getField(), "Capability field setting is broken");
    }
}
