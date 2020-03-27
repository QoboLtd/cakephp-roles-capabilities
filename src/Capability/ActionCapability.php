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

use Cake\Utility\Inflector;

final class ActionCapability extends AbstractCapability
{

    /**
     * Constructor method.
     *
     * @param string $resource Resource name
     * @param string $operation Operation type
     */
    public function __construct(string $resource, string $operation)
    {
        try {
            parent::__construct($resource, $operation);
        } catch (\LogicException $e) {
            // @ignoreException
        }

        $this->resource = $resource;
        $this->operation = $operation;
    }

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
        // cameCaseMethod -> under_score -> Human Form -> lowercase
        $action = strtolower(Inflector::humanize(Inflector::underscore($this->operation)));

        switch ($action) {
            case 'index':
                $action = 'list';
                break;
            case 'info':
            case 'changelog':
                $action = 'view ' . $action;
                break;
        }

        return sprintf('Allow %s', $action);
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
}
