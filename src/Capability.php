<?php
declare(strict_types=1);

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

use InvalidArgumentException;

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
        'description' => '',
    ];

    /**
     * Capability name
     * @var string
     */
    protected $name = '';

    /**
     * Capability label
     * @var string
     */
    protected $label = '';

    /**
     * Capability description
     * @var string
     */
    protected $description = '';

    /**
     * Capability field
     * @var string
     */
    protected $field = '';

    /**
     * Capability parent modules
     * @var array
     */
    protected $parentModules = [];

    /**
     * Constructor method
     * @param string $name    Capability name
     * @param mixed[]  $options Capability options
     */
    public function __construct(string $name, array $options = [])
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
     *
     * @throws \InvalidArgumentException if the name is empty
     * @param string $name Capability name
     * @return \RolesCapabilities\Capability
     */
    public function setName(string $name): \RolesCapabilities\Capability
    {
        if (empty($name)) {
            throw new InvalidArgumentException("Name cannot be empty");
        }
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label Capability label
     * @return \RolesCapabilities\Capability
     */
    public function setLabel(string $label = ''): \RolesCapabilities\Capability
    {
        $this->label = '' !== trim($label) ? $label : ucwords(str_replace('_', ' ', $this->name));

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set description
     *
     * @param string $description Capability description
     * @return \RolesCapabilities\Capability
     */
    public function setDescription(string $description): \RolesCapabilities\Capability
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set field
     *
     * @param string $field Capability field
     * @return \RolesCapabilities\Capability
     */
    public function setField(string $field): \RolesCapabilities\Capability
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Set parent module(s)
     *
     * @param mixed $parentModules Capability parent module(s)
     * @return \RolesCapabilities\Capability
     */
    public function setParentModules($parentModules): \RolesCapabilities\Capability
    {
        $this->parentModules = is_array($parentModules) ? $parentModules : [$parentModules];

        return $this;
    }

    /**
     * Get parent module(s)
     *
     * @return mixed[]
     */
    public function getParentModules(): array
    {
        return $this->parentModules;
    }
}
