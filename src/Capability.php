<?php
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
    protected $_default_options = [
        'label' => '',
        'description' => ''
    ];

    /**
     * Capability name
     * @var string
     */
    protected $_name;

    /**
     * Capability label
     * @var string
     */
    protected $_label;

    /**
     * Capability description
     * @var string
     */
    protected $_description;

    /**
     * Capability field
     * @var string
     */
    protected $_field;

    /**
     * Capability parent modules
     * @var array
     */
    protected $_parentModules = [];

    /**
     * Constructor method
     * @param string $name    Capability name
     * @param array  $options Capability options
     */
    public function __construct($name, array $options = [])
    {
        $this->setName($name);

        // set capability options
        $options = array_merge($this->_default_options, $options);

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
        return $this->_name;
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
        $this->_name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set label
     * @param string $label Capability label
     * @return Capability
     */
    public function setLabel($label = '')
    {
        $this->_label = '' !== trim($label) ? $label : ucwords(str_replace('_', ' ', $this->_name));

        return $this;
    }

    /**
     * Get label
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Set description
     * @param string $description Capability description
     * @return Capability
     */
    public function setDescription($description)
    {
        $this->_description = $description;

        return $this;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Set field
     *
     * @param string $field Capability field
     * @return Capability
     */
    public function setField($field)
    {
        $this->_field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Set parent module(s)
     *
     * @param string $parentModules Capability parent module(s)
     * @return Capability
     */
    public function setParentModules($parentModules)
    {
        $this->_parentModules = $parentModules;

        return $this;
    }

    /**
     * Get parent module(s)
     *
     * @return array
     */
    public function getParentModules()
    {
        return $this->_parentModules;
    }
}
