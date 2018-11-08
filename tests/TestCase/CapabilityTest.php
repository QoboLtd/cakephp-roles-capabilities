<?php
namespace RolesCapabilities\Test\TestCase;

use PHPUnit\Framework\TestCase;
use RolesCapabilities\Capability;

class CapabilityTest extends TestCase
{
    public function testConstructorOptions(): void
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

    public function testToString(): void
    {
        $cap = new Capability('Foobar');
        $result = (string)$cap;
        $this->assertEquals('Foobar', $result, "Capability casting to string is broken");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNameException(): void
    {
        $cap = new Capability('foobar');
        $cap->setName('');
    }

    public function testSetName(): void
    {
        $cap = new Capability('Blah');
        $cap->setName('Foobar');
        $this->assertEquals('Foobar', $cap->getName(), "Capability name setting is broken");
    }

    public function testSetlabel(): void
    {
        $cap = new Capability('Awesome_Capability');
        $cap->setLabel('My Capability');
        $this->assertEquals('My Capability', $cap->getLabel(), "Capability label setting is broken");

        $cap->setLabel('');
        $this->assertEquals('Awesome Capability', $cap->getLabel(), "Capability label fallback is broken");
    }

    public function testSetDescription(): void
    {
        $cap = new Capability('Foobar');
        $cap->setDescription('Blah');
        $this->assertEquals('Blah', $cap->getDescription(), "Capability description setting is broken");
    }

    public function testSetField(): void
    {
        $cap = new Capability('Foobar');
        $cap->setField('some_field');
        $this->assertEquals('some_field', $cap->getField(), "Capability field setting is broken");
    }
}
