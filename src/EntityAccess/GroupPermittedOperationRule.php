<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Association;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Groups\Model\Table\GroupsTable;
use RolesCapabilities\Model\Table\PermissionsTable;
use Webmozart\Assert\Assert;

/**
 * Rule to allow access if the operation is permitted for the group
 */
class GroupPermittedOperationRule implements AuthorizationRule
{
    private $subject;

    private $table;

    private $operation;

    private $entityId;

    /**
     * @param string $subject The subject (ie userId)
     * @param Table $table The table for this operation
     * @param string $operation The operation
     * @param ?string $entityId The id for the entity (if not list)
     */
    public function __construct(string $subject, Table $table, string $operation, ?string $entityId = null)
    {
        $this->subject = $subject;
        $this->table = $table;
        $this->operation = $operation;
        $this->entityId = $entityId;
    }

    /**
     * @inheritdoc
     */
    public function allow(): bool
    {
        if ($this->subject == null || $this->entityId === null) {
            return false;
        }

        $table = TableRegistry::getTableLocator()->get('RolesCapabilities.Permissions');
        Assert::isInstanceOf($table, PermissionsTable::class);

        $groups = TableRegistry::getTableLocator()->get('Groups.Groups');
        Assert::isInstanceOf($groups, GroupsTable::class);

        $entity = $table->find()
        ->where([
            'owner_model' => 'Groups',
            'model' => $this->table->getTable(),
            'owner_foreign_key' => $this->subject,
            'foreign_key' => $this->entityId,
            'type' => $this->operation,
        ])
        ->applyOptions(['accessCheck' => false])
        ->count() > 0;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): QueryExpression
    {
        $table = TableRegistry::getTableLocator()->get('RolesCapabilities.Permissions');
        Assert::isInstanceOf($table, PermissionsTable::class);

        $groups = TableRegistry::getTableLocator()->get('Groups.Groups');
        Assert::isInstanceOf($groups, GroupsTable::class);

        $conditions = [
            'owner_model' => 'Groups',
            'model' => $this->table->getTable(),
            'type' => $this->operation,
        ];

        if ($this->entityId !== null) {
            $conditions['foreign_key'] = $this->entityId;
        }

        $userGroups = $groups->find()->select(['id'])->matching('Users', function ($q) {
            return $q->where(['Users.id' => $this->subject]);
        });

        $conditions['owner_foreign_key IN '] = $userGroups;

        return $query->newExpr()->exists(
            $table->find()
                ->select(['foreign_key'])
                ->where(['foreign_key = ' . $this->table->aliasField('id')])
                ->where($conditions)
        );
    }
}
