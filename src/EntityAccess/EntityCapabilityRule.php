<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Database\Expression\QueryExpression;
use Cake\Log\LogTrait;
use Cake\ORM\Association;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Model\Table\ExtendedCapabilitiesTable;
use Webmozart\Assert\Assert;

/**
 * Rule to allow access if the operation is permitted for
 * any of the groups the subject is a part of.
 */
class EntityCapabilityRule implements AuthorizationRule
{
    use LogTrait;

    /**
     * @var SubjectInterface
     */
    private $subject;

    /**
     * @var Table
     */
    private $table;

    private $operation;

    private $entityId;

    /**
     * @param SubjectInterface $subject The subject (ie userId)
     * @param Table $table The resource for this operation
     * @param string $operation The operation
     */
    public function __construct(SubjectInterface $subject, Table $table, string $operation, ?string $entityId)
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
        if ($this->subject === null || $this->entityId === null) {
            return false;
        }

        $roles = $this->subject->getRoles();
        if (count($roles) === 0) {
            return false;
        }

        $table = TableRegistry::getTableLocator()->get('RolesCapabilities.ExtendedCapabilities');
        Assert::isInstanceOf($table, ExtendedCapabilitiesTable::class);

        $capabilities = $table->find()
        ->where([
            'resource' => $this->table->getTable(),
            'operation' => $this->operation,
        ])
        ->matching('Roles', function (Query $q) use ($roles) {
            return $q->where([
                'role_id IN' => $roles,
            ]);
        })
        ->applyOptions(['filterQuery' => false])
        ->order(['association']);

        foreach ($capabilities as $capability) {
            $associationName = $capability->get('association');
            if ($associationName === '') {
                return true;
            }

            if (!$this->table->hasAssociation($associationName)) {
                $this->log('Unknown association ' . $associationName . ' for Table ' . $this->table->getTable());
                continue;
            }

            $association = $this->table->getAssociation($associationName);
            $primaryKey = $association->getTarget()->getPrimaryKey();
            Assert::string($primaryKey);

            $query = $association->find()->where([$association->getTarget()->aliasField($primaryKey) => $this->subject ]);
        }

        $primaryKey = $this->table->getPrimaryKey();
        Assert::string($primaryKey);

        $query = $this->table->find()->applyOptions(['filterQuery' => true])->where([$this->table->aliasField($primaryKey) => $this->entityId]);

        $expression = $this->expression($query);
        if ($expression === null) {
            return true;
        }

        $entity = $query->applyOptions(['filterQuery' => true])->where($expression)->first();

        return $entity !== null;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): ?QueryExpression
    {
        $roles = $this->subject->getRoles();
        if (count($roles) === 0) {
            return null;
        }

        $table = TableRegistry::getTableLocator()->get('RolesCapabilities.ExtendedCapabilities');
        Assert::isInstanceOf($table, ExtendedCapabilitiesTable::class);

        $capabilities = $table->find()
        ->applyOptions(['filterQuery' => true])
        ->where([
            'resource' => $this->table->getTable(),
            'operation' => $this->operation,
        ])
        ->matching('Roles', function (Query $q) use ($roles) {
            return $q->where([
                'role_id IN' => $roles,
            ]);
        });

        $expressions = [];
        foreach ($capabilities as $capability) {
            $associationName = $capability->get('association');
            if ($associationName === '') {
                return null;
            }

            if (!$this->table->hasAssociation($associationName)) {
                $this->log('Unknown association ' . $associationName . ' for Table ' . $this->table->getTable());
                continue;
            }

            $association = $this->table->getAssociation($associationName);
            $primaryKey = $association->getTarget()->getPrimaryKey();
            Assert::string($primaryKey);

            $expressions[] = $association->getTarget()->query()->applyOptions(['filterQuery' => true])
                        ->where([$association->getTarget()->aliasField($primaryKey) => $this->subject ]);
        }

        $exp = $query->newExpr();
        foreach ($expressions as $expression) {
            $exp->exists($expression);
        }

        return $exp;
    }
}
