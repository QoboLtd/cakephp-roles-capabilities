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

interface CapabilityInterface
{
    /**
     * Capability name getter.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Capability description getter.
     *
     * @return string
     */
    public function getDescription() : string;

    /**
     * Enforced capabilities getter.
     *
     * @return \RolesCapabilities\Capability\CapabilityInterface[]
     */
    public function getEnforced() : array;

    /**
     * Overridden-by capabilities getter.
     *
     * @return \RolesCapabilities\Capability\CapabilityInterface[]
     */
    public function getOverriddenBy() : array;
}
