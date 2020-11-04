<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use RolesCapabilities\Access\Utils;
use RolesCapabilities\Model\Table\ExtendedCapabilitiesTable;
use RolesCapabilities\Model\Table\PermissionsTable;

class PolicyBuilder
{
    /**
     * @var Table
     */
    private $table;

    private $user;

    /**
     * @var ?string
     */
    private $entityId;

    /**
     * @var $operation
     */
    private $operation;

    /**
     * Creates a new policy builder.
     *
     * @param ?array $user The user
     * @param Table $table The entity class to check ()
     * @param string $operation The operation to perform (one of: list, create, edit, delete)
     * @param ?string $entityId The entity Id to check
     */
    public function __construct(?array $user, Table $table, string $operation, ?string $entityId)
    {
        $this->user = $user;
        $this->table = $table;
        $this->operation = $operation;
        $this->entityId = $entityId;
    }

    /**
     * Whether the user is a supervisor.
     *
     * @return bool
     */
    private function isSupervisor(): bool
    {
        if ($this->user === null || !isset($this->user['is_supervisor'])) {
            return false;
        }

        return (bool)$this->user['is_supervisor'];
    }

    /**
     * Whether the user is a superuser
     *
     * @return bool
     */
    private function isSuperuser(): bool
    {
        if (!isset($this->user['is_superuser'])) {
            return false;
        }

        return (bool)$this->user['is_superuser'];
    }

    /**
     * Builds the policy.
     *
     * @return AuthorizationRule A single rule expressing the policy
     */
    public function build(): AuthorizationRule
    {
        AuthorizationContextHolder::asSystem();
        try {
            return $this->doBuild();
        } finally {
            AuthorizationContextHolder::pop();
        }
    }

    /**
     * Actual building function to allow easy wrapping
     */
    private function doBuild(): AuthorizationRule
    {
        if (empty($this->user)) {
            return new DenyRule();
        }

        if ($this->isSuperuser()) {
            return new AllowRule();
        }

        $userRules = [
            new PermittedOperationRule($this->user['id'], $this->table, $this->operation, $this->entityId),
            new GroupPermittedOperationRule($this->user['id'], $this->table, $this->operation, $this->entityId),
        ];

        if ($this->isSupervisor()) {
            foreach (Utils::getReportToUsers($entityId) as $subordinate) {
                $builder = new PolicyBuilder($subordinate, $this->table, $this->operation, $this->entityId);
                $userRules[] = $builder->build();
            }
        }

        return MultiRule::any(...$userRules);
    }
}
