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
        $this->_label = '' !== trim($label) ?: ucwords(str_replace('_', ' ', $this->_name));

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
}
