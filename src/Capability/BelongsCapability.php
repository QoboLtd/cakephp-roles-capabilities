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

final class BelongsCapability extends AbstractCapability
{
    protected const SUPPORTED_OPERATIONS = [
        ResourceInterface::OPERATION_READ,
        ResourceInterface::OPERATION_UPDATE,
        ResourceInterface::OPERATION_DELETE
    ];

    /**
     * Field name.
     *
     * @var string
     */
    private $field = '';

    /**
     * Constructor method.
     *
     * @param string $resource Resource name
     * @param string $operation Operation type
     * @param string $field Field name
     */
    public function __construct(string $resource, string $operation, string $field)
    {
        parent::__construct($resource, $operation);

        $this->field = $field;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return sprintf('cap__%s__%s_%s', $this->resource, $this->operation, $this->field);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription() : string
    {
        return sprintf('Belongs to - through <i>%s</i> field', $this->field);
    }

    /**
     * {@inheritDoc}
     */
    public function getEnforced() : array
    {
        if (in_array($this->operation, [ResourceInterface::OPERATION_UPDATE, ResourceInterface::OPERATION_DELETE])) {
            return [new OwnerCapability($this->resource, ResourceInterface::OPERATION_READ, $this->field)];
        }

        return parent::getEnforced();
    }

    /**
     * {@inheritDoc}
     */
    public function getOverriddenBy() : array
    {
        return [new FullCapability($this->resource, $this->operation)];
    }

    /**
     * Field name getter.
     *
     * @return string
     */
    public function getField() : string
    {
        return $this->field;
    }
}
