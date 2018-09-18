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
namespace RolesCapabilities\Event\Model;

use ArrayObject;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Query;
use Qobo\Utils\Utility\User;
use RolesCapabilities\FilterQuery;

class ModelBeforeFindEventsListener implements EventListenerInterface
{
    /**
     * List target associations.
     *
     * @var array
     */
    protected $_targetAssociations = ['manyToMany', 'manyToOne'];

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.beforeFind' => [
                'callable' => 'filterByUserCapabilities',
                'priority' => PHP_INT_MAX - 1 // this callback should be executed as late as possible.
            ]
        ];
    }

    /**
     * Query filtering method based on current user capabilities.
     *
     * Filtering can be skipped on per query basis by passing the
     * 'accessCheck' option and set it to false.
     *
     * Additionally filtering can be skipped on per table basis by
     * defining the table name in the plugin's configuration under
     * variable 'RolesCapabilities.ownerCheck.skipTables'.
     *
     * @param \Cake\Event\Event $event The beforeFind event that was fired.
     * @param \Cake\ORM\Query $query Query
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function filterByUserCapabilities(Event $event, Query $query, ArrayObject $options)
    {
        if (isset($options['accessCheck']) && ! $options['accessCheck']) {
            return;
        }

        $filterQuery = new FilterQuery(
            $query,
            $event->getSubject(),
            User::getCurrentUser()
        );

        $filterQuery->execute();
    }
}
