<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Association;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Groups\Model\Table\GroupsTable;
use RolesCapabilities\Model\Table\ExtendedCapabilitiesTable;
use Webmozart\Assert\Assert;

/**
 * Rule to allow access if the operation is permitted for
 * any of the groups the subject is a part of.
 */
class ResourceCapabilityRule implements AuthorizationRule
{
    private $subject;

    private $resource;

    private $operation;

    /**
     * @param string $subject The subject (ie userId)
     * @param string $resource The resource for this operation could a table name or a controller
     * @param string $operation The operation
     */
    public function __construct(string $subject, string $resource, string $operation)
    {
        $this->subject = $subject;
        $this->resource = $resource;
        $this->operation = $operation;
    }

    /**
     * @inheritdoc
     */
    public function allow(): bool
    {
        $table = TableRegistry::getTableLocator()->get('RolesCapabilities.ExtendedCapabilities');
        Assert::isInstanceOf($table, ExtendedCapabilitiesTable::class);

        $roles = $table->getAssociation('Roles')->getTarget();

        $groups = $roles->get('Groups');
        Assert::isInstanceOf($groups, GroupsTable::class);
        $userGroups = $groups->find()->select(['id'])->matching('Users', function (Query $q) {
            return $q->where(['Users.id' => $this->subject]);
        });

        $userRoles = $roles->find()->select(['id'])->where(
            ['group_id IN' => $userGroups]
        );

        $capabilities = $table->find()
        ->where([
            'resource' => $this->resource,
            'association' => '',
            'operation' => $this->operation,
        ])
        ->matching('Roles', function (Query $q) use ($userRoles) {
            return $q->where([
                'role_id IN' => $userRoles,
            ]);
        })
        ->applyOptions(['accessCheck' => false])
        ->count() > 0;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): QueryExpression
    {
        $table = TableRegistry::getTableLocator()->get('RolesCapabilities.ExtendedCapabilities');
        Assert::isInstanceOf($table, ExtendedCapabilitiesTable::class);

        $roles = $table->getAssociation('Roles')->getTarget();

        $groups = $roles->get('Groups');
        Assert::isInstanceOf($groups, GroupsTable::class);
        $userGroups = $groups->find()->select(['id'])->matching('Users', function (Query $q) {
            return $q->where(['Users.id' => $this->subject]);
        });

        $userRoles = $roles->find()->select(['id'])->where(
            ['group_id IN' => $userGroups]
        );

        $capabilities = $table->find()
        ->where([
            'resource' => $this->resource,
            'association' => '',
            'operation' => $this->operation,
        ])
        ->matching('Roles', function (Query $q) use ($userRoles) {
            return $q->where([
                'role_id IN' => $userRoles,
            ]);
        });

        return $query->newExpr()->exists(
            $capabilities
        );
    }
}
