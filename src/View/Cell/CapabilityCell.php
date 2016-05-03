<?php
namespace RolesCapabilities\View\Cell;

use Cake\View\Cell;
use Cake\Utility\Inflector;

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
    public function display()
    {
    }

    /**
     * Method that converts Controller
     * full name to human readable label
     * @param  string $name Controller full name
     * @return void
     */
    public function groupName($name)
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
