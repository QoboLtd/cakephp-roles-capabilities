<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ValueBinder;
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
     * @var int
     */
    private $aliasCounter;

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

        $this->aliasCounter = 0;
    }

    /**
     * @return mixed[]
     */
    protected function getCapabilities(): array
    {
        $resource = $this->table->getRegistryAlias();

        $tableCapabilities = CapabilitiesUtil::getModelStaticCapabities($this->table);

        $roles = $this->subject->getRoles();
        if (count($roles) === 0) {
            return $tableCapabilities;
        }

        $table = TableRegistry::getTableLocator()->get('RolesCapabilities.ExtendedCapabilities');
        Assert::isInstanceOf($table, ExtendedCapabilitiesTable::class);

        $dynamicCapabilities = $table->find()
        ->where([
            'resource' => $resource,
            'operation' => $this->operation,
        ])
        ->matching('Roles', function (Query $q) use ($roles) {
            return $q->where([
                'role_id IN' => $roles,
            ]);
        })
        ->applyOptions(['filterQuery' => false])
        ->order(['association'])->toArray();

        return array_merge($tableCapabilities, $dynamicCapabilities);
    }

    /**
     * @inheritdoc
     */
    public function allow(): bool
    {
        if ($this->subject === null) {
            return false;
        }

        $primaryKey = $this->table->getPrimaryKey();
        Assert::string($primaryKey);

        $query = $this->table->query()
            ->applyOptions(['filterQuery' => true])
            ->select([$this->table->aliasField($primaryKey)]);

        $exp = $this->expression($query);

        $entity = $query->where($exp)->first();

        return $entity !== null;
    }

    /**
     * Create unique alias for the table. Each call returns a new alias.
     * This is to allow us to include the table in multiple subqueries
     * without ambiguity.
     *
     * @return string unique alias for this table
     */
    private function aliasTable(): string
    {
        $this->aliasCounter++;

        return $this->table->getAlias() . '_' . $this->aliasCounter;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): QueryExpression
    {
        $capabilities = $this->getCapabilities();

        $exp = $query->newExpr();
        $exp->setConjunction('OR');

        $isEmpty = true;

        $associations = CapabilitiesUtil::getModelCapabilityAssociations($this->table);

        foreach ($capabilities as $capability) {
            if (!isset($associations[$capability['association']])) {
                continue;
            }

            $association = $associations[$capability['association']];

            $associationName = $association['association'];
            if ($associationName === '') {
                return $query->newExpr("'EXTENDED_CAPS'='EXTENDED_CAPS'");
            }

            /**
             * Fake field association. Simply match field value.
             */
            if ($associationName === 'field') {
                $isEmpty = false;
                $exp->eq($this->table->aliasField($association['field']), $this->subject->getId());
                continue;
            }

            if (!$this->table->hasAssociation($associationName)) {
                error_log('Unknown association ' . $associationName . ' for Table ' . $this->table->getTable());
                continue;
            }
            $isEmpty = false;

            $association = $this->table->getAssociation($associationName);
            $primaryKey = $association->getTarget()->getPrimaryKey();
            Assert::string($primaryKey);

            $sourcePrimaryKey = $this->table->getPrimaryKey();
            Assert::string($sourcePrimaryKey);

            $driver = $this->table->getConnection()->getDriver();
            $quotedAlias = $driver->quoteIdentifier($this->aliasTable());
            $quotedSubject = $driver->quote($this->subject->getId(), \PDO::PARAM_STR);

            /*
             * Create inner query. We cannot use parameters since we are creating
             * an sql string.
             */
            $innerQuery = $this->table->query()
                ->applyOptions(['filterQuery' => true ])
                ->select([$this->table->aliasField($sourcePrimaryKey) ])
                ->where($quotedAlias . '.' . $sourcePrimaryKey . '=' . $this->table->aliasField($sourcePrimaryKey));

            if ($this->entityId !== null) {
                $innerQuery->where([$this->table->aliasField($sourcePrimaryKey) => $this->entityId]);
            }

            $innerQuery->matching($associationName, function ($q) use ($association, $primaryKey, $quotedSubject) {
                return $q->where($association->getTarget()->aliasField($primaryKey) . ' = ' . $quotedSubject);
            });

            $sql = $innerQuery->sql();

            $quotedTable = $driver->quoteIdentifier($this->table->getAlias());

            /* Replace the table with it's alias. This is to distinguish this subquery
             * from other subqueries and the parent
            */
            $sql = str_replace($quotedTable, $quotedAlias, $sql);

            /* Append the raw query to our expressions */
            $exp->exists($query->newExpr($sql));
        }

        if ($isEmpty) {
            return $query->newExpr("'EXTENDED_CAPS'='NONE'");
        }

        return $exp;
    }
}
