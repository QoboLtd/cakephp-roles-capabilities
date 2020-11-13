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
            ['operation' => Operation::VIEW, 'association' => 'Users', 'name' => 'Member Of'],
        ],
        'Groups.GroupsUsers' => [
            ['operation' => Operation::VIEW, 'association' => 'field', 'field' => 'user_id', 'name' => 'Group Memberships'],
        ],
        'RolesCapabilities.Roles' => [
            ['operation' => Operation::VIEW, 'association' => 'Groups.Users', 'name' => 'User Roles'],
        ],
    ];

    /**
     * @return mixed[]
     */
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

    /**
     * @return mixed[]
     */
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

        $primaryKey = $this->table->getPrimaryKey();
        Assert::string($primaryKey);

        $query = $this->table->query()
            ->applyOptions(['filterQuery' => true])
            ->select([$this->table->aliasField($primaryKey)])
            ->where([$this->table->aliasField($primaryKey) => $this->entityId]);

        $exp = $this->expression($query);

        $entity = $query->where($exp)->first();

        return $entity !== null;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): QueryExpression
    {
        $capabilities = $this->getCapabilities();

        $expressions = [];
        foreach ($capabilities as $capability) {
            $associationName = $capability['association'];
            if ($associationName === '') {
                return $query->newExpr("'EXTENDED_CAPS'='EXTENDED_CAPS'");
            }

            if ($associationName === 'field') {
                $expressions[] = $query->newExpr()->eq($this->table->aliasField($capability['field']), $this->subject->getId());
                continue;
            }

            if (!$this->table->hasAssociation($associationName)) {
                error_log('Unknown association ' . $associationName . ' for Table ' . $this->table->getTable());
                continue;
            }

            $association = $this->table->getAssociation($associationName);
            $primaryKey = $association->getTarget()->getPrimaryKey();
            Assert::string($primaryKey);

            $sourcePrimaryKey = $this->table->getPrimaryKey();
            Assert::string($sourcePrimaryKey);

            $aliasedField = $sourcePrimaryKey . '__alias';
            $innerQuery = $this->table->query()
            ->applyOptions(['filterQuery' => true ])
            ->select([ $aliasedField => $this->table->aliasField($sourcePrimaryKey) ]);

            $association->attachTo($innerQuery, ['includeFields' => false ]);

            $innerQuery
                ->where([$this->table->aliasField($sourcePrimaryKey) . '=' . $aliasedField ])
                ->where([$association->getTarget()->aliasField($primaryKey) => $this->subject->getId()]);

            error_log(print_r($innerQuery, true));
            $expressions[] = $innerQuery;
        }

        if (count($expressions) === 0) {
            return $query->newExpr("'EXTENDED_CAPS'='NONE'");
        }

        $exp = $query->newExpr();
        $exp->setConjunction('OR');

        foreach ($expressions as $expression) {
            $exp->add($expression);
        }

        return $exp;
    }
}
