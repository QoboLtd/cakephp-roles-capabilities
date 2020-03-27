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

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Query;
use RolesCapabilities\FilterQuery;
use Webmozart\Assert\Assert;

/**
 * RESOURCE/CRUD OPERATIONS
 * ------------------------
 *
 * Create:
 *     What: Prevents entity creation if user role(s) do *not* include full access capability.
 *     When: beforeSave event, if is a new entity.
 *     Status: pending.
 *
 * Read:
 *     What: Adjusts query according to capability type(s).
 *     When: beforeFind event.
 *     Status: implemented (already covered by FilterQuery::class).
 *
 * Update:
 *     What: Prevents entity update if none of user role(s) capabilities are matched.
 *     When: beforeSave event, if is an existing entity.
 *     Status: pending.
 *
 * Delete:
 *     What: Prevents entity deletion if none of user role(s) capabilities are matched.
 *     When: beforeDelete event.
 *     Status: pending.
 *
 */
class AccessControlListener implements EventListenerInterface
{
    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.beforeFind' => [
                'callable' => 'beforeFind',
                'priority' => PHP_INT_MAX // this callback should be executed as late as possible
            ],
            'Model.beforeSave' => [
                'callable' => 'beforeSave',
                'priority' => -PHP_INT_MAX // this callback should be executed as early as possible
            ],
            'Model.beforeDelete' => [
                'callable' => 'beforeDelete',
                'priority' => -PHP_INT_MAX // this callback should be executed as early as possible
            ]
        ];
    }

    public function beforeFind(Event $event, Query $query, \ArrayObject $options) : void
    {
        if (isset($options['accessCheck']) && false === $options['accessCheck']) {
            return;
        }

        $table = $event->getSubject();
        Assert::isInstanceOf($table, \Cake\ORM\Table::class);

        (new FilterQuery($query, $table, User::getCurrentUser()))->execute();
    }

    public function beforeSave(Event $event, EntityInterface $entity, \ArrayObject $options) : void
    {
        if (isset($options['accessCheck']) && false === $options['accessCheck']) {
            return;
        }

        throw new \LogicException('To be implemented.');

        $allow = true; // Check goes here
        if (true === $allow) {
            return;
        }

        $event->stopPropagation();
    }

    public function beforeDelete(Event $event, EntityInterface $entity, \ArrayObject $options) : void
    {
        if (isset($options['accessCheck']) && false === $options['accessCheck']) {
            return;
        }

        throw new \LogicException('To be implemented.');

        $allow = true; // Check goes here
        if (true === $allow) {
            return;
        }

        $event->stopPropagation();
    }
}
