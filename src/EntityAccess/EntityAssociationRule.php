<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Association;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Webmozart\Assert\Assert;

/**
 * Rule to allow access if an association exists between
 * the entity and the subject.
 */
class EntityAssociationRule implements AuthorizationRule
{
    /**
     * @var string
     */
    private $subject;

    private $table;

    private $accociation;

    private $entityId;

    /**
     *
     */
    public function __construct(string $subject, Table $table, string $association, string $entityId)
    {
        $this->subject = $subject;
        $this->table = $table;
        $this->association = $association;
        $this->entityId = $entityId;
    }

    /**
     * Gets the association
     */
    private function getAssociation(): ?Association
    {
        return $this->table->getAssociation($this->association);
    }

    /**
     * @inheritdoc
     */
    public function allow(): bool
    {
        $accociation = $this->getAssociation();
        $table = $accociation->getTarget();
        $field = $accociation->getBindingKey();
        $foreignKey = $association->getForeignKey();
        Assert::isInstanceOf($field, string);

        return $table->find()->where([
            $field => $this->entityId,
            $foreignKey => $subject,
        ])
        ->applyOptions(['accessCheck' => false])
        ->count() > 0;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): QueryExpression
    {
        if ($this->subject == null) {
            return $query->newExpr('0=1');
        }

        $accociation = $this->getAssociation();
        $table = $accociation->getTarget();
        $field = $accociation->getBindingKey();
        Assert::isInstanceOf($field, string);

        $exp = $table->query()->newExpr()
        ->exists([
            $field => $this->entityId,
            $this->field => $subject,
        ]);

        return $exp;
    }
}
