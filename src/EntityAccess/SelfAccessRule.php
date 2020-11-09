<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use CakeDC\Users\Model\Table\UsersTable;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Association;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Model\Table\PermissionsTable;
use Webmozart\Assert\Assert;

/**
 * Rule to allow access to own user record
 */
class SelfAccessRule implements AuthorizationRule
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
     * Checks whether this is the user table.
     *
     * @return bool
     */
    protected function isUsersTable(): bool {
        return $this->table === TableRegistry::getTableLocator()->get('RolesCapabilities.Users');
    }

    /**
     * @inheritdoc
     */
    public function allow(): bool
    {
        if ($this->entityId === null) {
            return false;
        }

        return ($this->isUsersTable()) && $this->entityId === $this->subject->getId();
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): QueryExpression
    {
        if (!($this->isUsersTable())) {
            return $query->newExpr('0=1');
        }

        $exp = $query->newExpr(['id' => $this->subject->getId() ]);

        return $exp;
    }
}
