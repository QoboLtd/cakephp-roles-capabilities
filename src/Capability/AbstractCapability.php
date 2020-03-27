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
namespace RolesCapabilities\Capability;

abstract class AbstractCapability implements CapabilityInterface
{
    protected const SUPPORTED_OPERATIONS = [];

    /**
     * Resource name.
     *
     * @var string
     */
    protected $resource = '';

    /**
     * Operation type.
     *
     * @var string
     */
    protected $operation = '';

    /**
     * Constructor method.
     *
     * @param string $resource Resource name
     * @param string $operation Operation type
     */
    public function __construct(string $resource, string $operation)
    {
        if (! in_array($operation, static::SUPPORTED_OPERATIONS, true)) {
            throw new \RuntimeException(sprintf('"%s" operation is not supported by "%s"', $operation, __CLASS__));
        }

        $this->resource = $resource;
        $this->operation = $operation;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnforced() : array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getOverriddenBy() : array
    {
        return [];
    }

    /**
     * Helper method for returning enforced capabilities names.
     *
     * @return string[]
     */
    public function getEnforcedNames() : array
    {
        return array_map(function ($item) {
            return $item->getName();
        }, $this->getEnforced());
    }

    /**
     * Helper method for returning enforced capabilities names.
     *
     * @return string[]
     */
    public function getOverriddenByNames() : array
    {
        return array_map(function ($item) {
            return $item->getName();
        }, $this->getOverriddenBy());
    }
}
