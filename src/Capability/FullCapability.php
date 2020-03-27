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

final class FullCapability extends AbstractCapability
{
    protected const SUPPORTED_OPERATIONS = [
        ResourceInterface::OPERATION_CREATE,
        ResourceInterface::OPERATION_READ,
        ResourceInterface::OPERATION_UPDATE,
        ResourceInterface::OPERATION_DELETE
    ];

    /**
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return sprintf('cap__%s__%s', $this->resource, $this->operation);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription() : string
    {
        return 'All';
    }

    /**
     * {@inheritDoc}
     */
    public function getEnforced() : array
    {
        if (in_array($this->operation, [ResourceInterface::OPERATION_UPDATE, ResourceInterface::OPERATION_DELETE])) {
            return [new FullCapability($this->resource, ResourceInterface::OPERATION_READ)];
        }

        return parent::getEnforced();
    }
}
