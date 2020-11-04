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
namespace RolesCapabilities\EntityAccess\Event;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Qobo\Utils\Utility\User;
use RolesCapabilities\EntityAccess\AllowRule;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\AuthorizationRule;
use RolesCapabilities\EntityAccess\PolicyBuilder;
use Webmozart\Assert\Assert;

class QueryFilterEventsListener implements EventListenerInterface
{

    /**
     * @var ?array
     */
    private $user;

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.beforeFind' => 'beforeFind',
            'Model.beforeDelete' => 'beforeDelete',
            'Model.beforeSave' => 'beforeSave',
        ];
    }

    /**
     * Creates authorization policy
     */
    private function policy(Event $event, ?EntityInterface $entity = null, string $operation = 'view'): ?AuthorizationRule
    {
        $ctx = AuthorizationContextHolder::context();
        if ($ctx === null || $ctx->system()) {
            return null;
        }

        // TODO Create configuration whitelist
        $req = $ctx->request();
        if ($req != null) {
            $controller = $req->getParam('controller');
            $action = $req->getParam('action');

            if ($controller === 'Users' && $action === 'login') {
                return null;
            }
        }

        $user = $ctx->subject();
        $table = $event->getSubject();
        Assert::isInstanceOf($table, Table::class);

        $entityId = null;
        if ($entity != null) {
            $primaryKey = $table->getPrimaryKey();

            if (!is_string($primaryKey)) {
                throw new \RuntimeException('Unsupported primary key');
            }

            $entityId = $entity->get($primaryKey);
        }

        $builder = new PolicyBuilder($user, $table, $operation, $entityId);

        return $builder->build();
    }

    private function allow(Event $event, ?EntityInterface $entity, string $operation): bool
    {
        AuthorizationContextHolder::asSystem();
        try {
            $policy = $this->policy($event, $entity, $operation);
            if ($policy === null) {
                return true;
            }

            return $policy->allow();
        } finally {
            AuthorizationContextHolder::pop();
        }
    }

    /**
     * Query filtering method based on current user capabilities.
     *
     * Filtering can be skipped on per query basis by passing the
     * 'accessCheck' option and set it to false.
     *
     * @param \Cake\Event\Event $event The beforeFind event that was fired.
     * @param \Cake\ORM\Query $query Query
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function beforeFind(Event $event, Query $query, ArrayObject $options): void
    {
        AuthorizationContextHolder::asSystem();
        try {
            $policy = $this->policy($event, null, 'view');
            if ($policy === null) {
                return;
            }

            $expression = $policy->expression($query);
        } finally {
            AuthorizationContextHolder::pop();
        }

        $query->where($expression);
    }

    /**
     * @return void
     */
    public function beforeDelete(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!$this->allow($event, $entity, 'delete')) {
            throw new \RuntimeException('Denied');
        }
    }

    /**
     * Handles Model.beforeSave
     *
     * @param Event $event The event
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew()) {
            $operation = 'create';
        } else {
            $operation = 'edit';
        }

        if (!$this->allow($event, $entity, $operation)) {
            throw new \RuntimeException('Denied');
        }
    }
}
