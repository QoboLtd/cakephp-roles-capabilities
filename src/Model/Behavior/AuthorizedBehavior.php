<?php
declare(strict_types=1);

namespace RolesCapabilities\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;
use RolesCapabilities\EntityAccess\AllowRule;
use RolesCapabilities\EntityAccess\AuthorizationContext;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\AuthorizationRule;
use RolesCapabilities\EntityAccess\Operation;
use RolesCapabilities\EntityAccess\PolicyBuilder;
use Webmozart\Assert\Assert;

class AuthorizedBehavior extends Behavior
{
    private static $ASSOCIATIONS = [
        '' => 'All',
    ];

    /**
     * Gets the associations involved in access control
     * for this table.
     *
     * @return mixed[]
     */
    public function getAssociations(): array
    {
        return array_merge(self::$ASSOCIATIONS, $this->getConfig('associations', []));
    }

    /**
     * Gets the operations for capability access
     * for this table.
     *
     * @return mixed[]
     */
    public function getOperations(): array
    {
        return array_merge(Operation::values(), $this->getConfig('operations', []));
    }

    /**
     * Gets capabilities granted for everyone.
     * @return mixed[]
     */
    public function getCapabilities(): array
    {
        return $this->getConfig('capabilities', []);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
    }

    /**
     * Filters query.
     */
    public function beforeFind(Event $event, Query $query, ArrayObject $options, bool $primary): void
    {
        if (isset($options['filterQuery'])) {
            return;
        }

        $ctx = AuthorizationContextHolder::context();
        if ($ctx === null || $ctx->system()) {
            return;
        }

        $req = $ctx->request();
        if ($req != null && $this->isAuthenticationRequest($req)) {
            return;
        }

        AuthorizationContextHolder::asSystem();
        try {
            $policy = $this->policy($ctx, $event, null, Operation::VIEW);

            $expression = $policy->expression($query);
            if ($expression !== null) {
                $query->where($expression);
            }
        } finally {
            AuthorizationContextHolder::pop();
        }
    }

    /**
     * Triggered after a new entity is saved in the users table. This function checks
     * if the user is new, and if so sends a notification to this user.
     *
     * @param Event $event Event trigger for afterSave by CakePHP Behaviors framework.
     * @param EntityInterface $entity The entity saved.
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew()) {
            $operation = Operation::CREATE;
        } else {
            $operation = Operation::EDIT;
        }

        if (!$this->allow($event, $entity, $operation)) {
            throw new \RuntimeException('Denied');
        }
    }

    /**
     * @return void
     */
    public function beforeDelete(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!$this->allow($event, $entity, Operation::DELETE)) {
            throw new \RuntimeException('Denied');
        }
    }

    /**
     * Checks whether the plugin matches the action plugin.
     *
     * @param mixed[] $publicAction The action
     * @param ?string $plugin The plugin
     *
     * @return bool
     */
    private function pluginMatch(array $publicAction, ?string $plugin): bool
    {
        return (
                   (empty($publicAction['plugin']) && empty($plugin))
                || (!empty($publicAction['plugin']) && $publicAction['plugin'] === $plugin)
                );
    }

    /**
     * Checks whether this is an authentication request.
     * @param ServerRequest $request The request to check
     *
     * @return bool
     */
    public function isAuthenticationRequest(ServerRequest $request): bool
    {
        $publicActions = Configure::read('RolesCapabilities.authorizationActions');
        if (empty($publicActions) || !is_array($publicActions)) {
            return false;
        }

        $plugin = $request->getParam('plugin');
        $controller = $request->getParam('controller');
        $action = $request->getParam('action');

        foreach ($publicActions as $publicAction) {
            if (
                $this->pluginMatch($publicAction, $plugin)
                && $publicAction['controller'] === $controller
                && $publicAction['action'] === $action
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates authorization policy
     *
     * @return AuthorizationRule
     */
    private function policy(AuthorizationContext $ctx, Event $event, ?EntityInterface $entity = null, string $operation): AuthorizationRule
    {
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

    /**
     * Helper function to check if the operation is allowed
     * @param Event $event The event to check
     * @param ?EntityInterface $entity The entity for the event (if any)
     * @param string $operation The operation
     *
     * @return bool
     */
    private function allow(Event $event, ?EntityInterface $entity, string $operation): bool
    {
        $ctx = AuthorizationContextHolder::context();
        if ($ctx === null || $ctx->system()) {
            return true;
        }

        $req = $ctx->request();
        if ($req != null && $this->isAuthenticationRequest($req)) {
            return true;
        }

        AuthorizationContextHolder::asSystem();
        try {
            $policy = $this->policy($ctx, $event, $entity, $operation);

            return $policy->allow();
        } finally {
            AuthorizationContextHolder::pop();
        }
    }
}
