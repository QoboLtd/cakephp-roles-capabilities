<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Database\Expression\QueryExpression;
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
    /**
     * @var SubjectInterface
     */
    private $subject;

    private $table;

    private $operation;

    private $entityId;

    /**
     * @param SubjectInterface $subject The subject
     * @param Table $table The table
     * @param string $operation The operation
     * @param ?string $entityId The entityId
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
        $query = $this->table->query();
        $exp = $this->expression($query);
        $query = $query->where($exp);

        $entity = $query->first();

        return $entity !== null;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): QueryExpression
    {
        $permissions = TableRegistry::getTableLocator()->get('RolesCapabilities.Permissions');
        Assert::isInstanceOf($permissions, PermissionsTable::class);

        $conditions = [
            'owner_model' => 'Users',
            'model' => $this->table->getTable(),
            'owner_foreign_key' => $this->subject->getId(),
            'type' => $this->operation,
        ];

        if ($this->entityId !== null) {
            $conditions['foreign_key'] = $this->entityId;
        }

        $primaryKey = $this->table->getPrimaryKey();
        Assert::string($primaryKey);

        $expression = $permissions->query()->applyOptions(['filterQuery' => true])
                ->select(['foreign_key'])
                ->where('foreign_key = ' . $this->table->aliasField($primaryKey))
                ->where($conditions);

        return $query->newExpr()->exists(
            $expression
        );
    }
}
