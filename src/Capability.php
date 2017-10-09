<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace RolesCapabilities;

/**
 * Capability Class
 */
class Capability
{
    /**
     * default options
     * @var array
     */
    protected $defaultOptions = [
        'label' => '',
        'description' => ''
    ];

    /**
     * Capability name
     * @var string
     */
    protected $name;

    /**
     * Capability label
     * @var string
     */
    protected $label;

    /**
     * Capability description
     * @var string
     */
    protected $description;

    /**
     * Capability field
     * @var string
     */
    protected $field;

    /**
     * Capability parent modules
     * @var array
     */
    protected $parentModules = [];

    /**
     * Constructor method
     * @param string $name    Capability name
     * @param array  $options Capability options
     */
    public function __construct($name, array $options = [])
    {
        $this->setName($name);

        // set capability options
        $options = array_merge($this->defaultOptions, $options);

        $this->setLabel($options['label']);

        $this->setDescription($options['description']);

        if (isset($options['field'])) {
            $this->setField($options['field']);
        }

        if (isset($options['parent_modules'])) {
            $this->setParentModules($options['parent_modules']);
        }
    }

    /**
     * toString method
     * @return string Capability name
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name Capability name
     * @return Capability
     */
    public function setName($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new \InvalidArgumentException();
        }
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     * @param string $label Capability label
     * @return Capability
     */
    public function setLabel($label = '')
    {
        $this->label = '' !== trim($label) ? $label : ucwords(str_replace('_', ' ', $this->name));

        return $this;
    }

    /**
     * Get label
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set description
     * @param string $description Capability description
     * @return Capability
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set field
     *
     * @param string $field Capability field
     * @return Capability
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set parent module(s)
     *
     * @param string $parentModules Capability parent module(s)
     * @return Capability
     */
    public function setParentModules($parentModules)
    {
        $this->parentModules = $parentModules;

        return $this;
    }

    /**
     * Get parent module(s)
     *
     * @return array
     */
    public function getParentModules()
    {
        return $this->parentModules;
    }
}
