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
namespace RolesCapabilities\View\Cell;

use Cake\Utility\Inflector;
use Cake\View\Cell;

/**
 * Capability cell
 */
class CapabilityCell extends Cell
{

    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array
     */
    protected $_validCellOptions = [];

    /**
     * Default display method.
     *
     * @return void
     */
    public function display(): void
    {
    }

    /**
     * Method that converts Controller
     * full name to human readable label
     * @param  string $name Controller full name
     * @return void
     */
    public function groupName(string $name): void
    {
        $parts = array_map(
            function ($n) {
                return Inflector::humanize(Inflector::underscore(str_replace('Controller', '', $n)));
            },
            explode('\\', $name)
        );

        /*
        removes empty array entries
         */
        $parts = array_filter($parts);

        /*
        get just the controller and plugin names
         */
        $parts = array_slice($parts, -2);

        $name = implode(' :: ', $parts);

        $this->set('groupName', $name);
    }
}
