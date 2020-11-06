<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Association;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Model\Table\PermissionsTable;
use Webmozart\Assert\Assert;

/**
 * Rule to allow access if an association exists between
 * the entity and the subject.
 */
class PermittedOperationRule implements AuthorizationRule
{
    private $subject;

    private $table;

    private $operation;

    private $entityId;

    /**
     * @param string $subject The subject
     * @param Table $table The table
     * @param string $operation The operation
     * @param ?string $entityId The entityId
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
        if ($this->entityId === null) {
            return false;
        }

        $table = TableRegistry::getTableLocator()->get('RolesCapabilities.Permissions');
        Assert::isInstanceOf($table, PermissionsTable::class);

        $entity = $table->find()
        ->where([
            'owner_model' => 'Users',
            'model' => $this->table,
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

        $conditions = [
            'owner_model' => 'Users',
            'model' => $this->table->getTable(),
            'owner_foreign_key' => $this->subject,
            'type' => $this->operation,
        ];

        if ($this->entityId !== null) {
            $conditions['foreign_key'] = $this->entityId;
        }

        return $query->newExpr()->exists(
            $table->find()->select(['foreign_key'])
                ->where('foreign_key = ' . $this->table->aliasField('id'))
                ->where($conditions)
        );
    }
}
