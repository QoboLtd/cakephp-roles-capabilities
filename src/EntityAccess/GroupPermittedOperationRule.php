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
 * Rule to allow access if the operation is permitted for
 * any of the groups the subject is a part of.
 */
class GroupPermittedOperationRule implements AuthorizationRule
{
    /**
     * @var SubjectInterface
     */
    private $subject;

    private $table;

    private $operation;

    private $entityId;

    /**
     * @param SubjectInterface $subject The subject
     * @param Table $table The table for this operation
     * @param string $operation The operation
     * @param ?string $entityId The id for the entity (if not list)
     */
    public function __construct(SubjectInterface $subject, Table $table, string $operation, ?string $entityId = null)
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

        $permissions = TableRegistry::getTableLocator()->get('RolesCapabilities.Permissions');
        Assert::isInstanceOf($permissions, PermissionsTable::class);

        $userGroups = $this->subject->getGroups();

        $entity = $permissions->find()
        ->applyOptions(['filterQuery' => true])
        ->where([
            'owner_model' => 'Groups',
            'model' => $this->table->getTable(),
            'owner_foreign_key IN' => $userGroups,
            'foreign_key' => $this->entityId,
            'type' => $this->operation,
        ])
        ->count() > 0;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): ?QueryExpression
    {
        $userGroups = $this->subject->getGroups();
        if (count($userGroups) === 0) {
            return null;
        }

        $permissions = TableRegistry::getTableLocator()->get('RolesCapabilities.Permissions');
        Assert::isInstanceOf($permissions, PermissionsTable::class);

        $conditions = [
            'owner_model' => 'Groups',
            'model' => $this->table->getTable(),
            'type' => $this->operation,
        ];

        if ($this->entityId !== null) {
            $conditions['foreign_key'] = $this->entityId;
        }

        $conditions['owner_foreign_key IN'] = $userGroups;

        error_log($query->sql());

        return $query->newExpr()->exists(
            $permissions->query()->applyOptions(['filterQuery' => true])
                ->select(['foreign_key'])
                ->where(['foreign_key = ' . $this->table->aliasField('id')])
                ->where($conditions)
        );
    }
}
