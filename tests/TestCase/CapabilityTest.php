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
            'parent_modules' => ['Foo', 'Bar'],
        ];
        $cap = new Capability('Blah', $options);
        $this->assertEquals('Foobar', $cap->getLabel(), "Constructor option for label is broken");
        $this->assertEquals('Foobar Capability', $cap->getDescription(), "Constructor option for description is broken");
        $this->assertEquals('some_field', $cap->getField(), "Constructor option for field is broken");

        $result = $cap->getParentModules();
        $this->assertTrue(is_array($result), "Capability parent module setting is broken for array types");
        $this->assertEquals(['Foo', 'Bar'], $result, "Capability parent module setting is broken for array values");
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

    public function testGetParentModules(): void
    {
        $cap = new Capability('Foobar');
        $result = $cap->getParentModules();
        $this->assertTrue(is_array($result), "getParentModules() returned a non-array result");
        $this->assertTrue(empty($result), "getParentModules() returned a non-empty result");
    }

    public function testSetParentModules(): void
    {
        $cap = new Capability('Foobar');
        $cap = $cap->setParentModules('Blah');
        $result = $cap->getParentModules();
        $this->assertTrue(is_array($result), "Capability parent module setting is broken for string types");
        $this->assertEquals(['Blah'], $result, "Capability parent module setting is broken for string values");

        $cap = new Capability('Foobar');
        $cap = $cap->setParentModules(['Foo', 'Bar']);
        $result = $cap->getParentModules();
        $this->assertTrue(is_array($result), "Capability parent module setting is broken for array types");
        $this->assertEquals(['Foo', 'Bar'], $result, "Capability parent module setting is broken for array values");
    }
}
