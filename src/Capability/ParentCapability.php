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

use RolesCapabilities\Access\ResourceInterface;

final class ParentCapability extends AbstractCapability
{
    protected const SUPPORTED_OPERATIONS = [
        ResourceInterface::OPERATION_READ
    ];

    private $parentModules = '';

    /**
     * Constructor method.
     *
     * @param string $resource Resource name
     * @param string $operation Operation type
     * @param string[] $parentModules Parent module names
     */
    public function __construct(string $resource, string $operation, array $parentModules)
    {
        parent::__construct($resource, $operation);

        $this->parentModules = $parentModules;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return sprintf('cap__%s__fetch_parent', $this->resource);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription() : string
    {
        return sprintf('Parent - if owner on module(s): <i>%s</i>', implode(', ', $this->parentModules));
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
        return [new FullCapability($this->resource, $this->operation)];
    }

    /**
     * Parent modules getter.
     *
     * @return string[]
     */
    public function getParentModules() : array
    {
        return $this->parentModules;
    }
}
