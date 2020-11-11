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

    private const CAPS = [
        'Groups.Groups' => [
            ['operation' => Operation::VIEW, 'association' => 'users', 'name' => 'Member Of'],
        ],
       /* 'RolesCapabilities.Roles' => [
            ['operation' => Operation::VIEW, 'association' => 'Groups.Users'],
        ],
        */
    ];

    protected function getStaticCapabilities(string $resource, string $operation): array
    {
        if (!isset(self::CAPS[$resource])) {
            return [];
        }

        $caps = [];
        foreach (self::CAPS[$resource] as $cap) {
            if ($cap['operation'] === $operation) {
                $caps[] = $cap;
            }
        }

        return $caps;
    }

    protected function getCapabilities(): array
    {
        $resource = $this->table->getRegistryAlias();

        $staticCapabilities = $this->getStaticCapabilities($resource, $this->operation);

        $roles = $this->subject->getRoles();
        if (count($roles) === 0) {
            return $staticCapabilities;
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

        return array_merge($staticCapabilities, $dynamicCapabilities);
    }

    /**
     * @inheritdoc
     */
    public function allow(): bool
    {
        if ($this->subject === null || $this->entityId === null) {
            return false;
        }

        $capabilities = $this->getCapabilities();

        $expressions = [];
        foreach ($capabilities as $capability) {
            $associationName = $capability['association'];
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

            $expressions[] = $association->find()->where([$association->getTarget()->aliasField($primaryKey) => $this->subject->getId() ]);
        }

        if (count($expressions) === 0) {
            return false;
        }

        $primaryKey = $this->table->getPrimaryKey();
        Assert::string($primaryKey);

        $query = $this->table->query()->applyOptions(['filterQuery' => true])
            ->where([$this->table->aliasField($primaryKey) => $this->entityId]);

        $exp = $query->newExpr();
        $exp->setConjunction('OR');

        foreach ($expressions as $expression) {
            $exp->exists($expression);
        }

        $entity = $query->where($exp)->first();

        return $entity !== null;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): ?QueryExpression
    {
        $capabilities = $this->getCapabilities();

        $expressions = [];
        foreach ($capabilities as $capability) {
            $associationName = $capability['association'];
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
                        ->where([$association->getTarget()->aliasField($primaryKey) => $this->subject->getId() ]);
        }

        if (count($expressions) === 0) {
            return $query->newExpr('1=0');
        }

        $exp = $query->newExpr();
        $exp->setConjunction('OR');

        foreach ($expressions as $expression) {
            $exp->exists($expression);
        }

        return $exp;
    }
}
