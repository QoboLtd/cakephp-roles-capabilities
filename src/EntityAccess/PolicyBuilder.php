<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\ORM\Table;

class PolicyBuilder
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var ?SubjectInterface
     */
    private $subject;

    /**
     * @var ?string
     */
    private $entityId;

    /**
     * @var string
     */
    private $operation;

    /**
     * Creates a new policy builder.
     *
     * @param ?SubjectInterface $subject The subject
     * @param Table $table The entity class to check ()
     * @param string $operation The operation to perform (one of: list, create, edit, delete)
     * @param ?string $entityId The entity Id to check
     */
    public function __construct(?SubjectInterface $subject, Table $table, string $operation, ?string $entityId)
    {
        $this->subject = $subject;
        $this->table = $table;
        $this->operation = $operation;
        $this->entityId = $entityId;
    }

    /**
     * Builds the policy.
     *
     * @return AuthorizationRule A single rule expressing the policy
     */
    public function build(): AuthorizationRule
    {
        if ($this->subject === null) {
            return new DenyRule();
        }

        if ($this->subject->isSuperuser()) {
            return new AllowRule();
        }

        $userRules = [
            new PermittedOperationRule($this->subject, $this->table, $this->operation, $this->entityId),
            new GroupPermittedOperationRule($this->subject, $this->table, $this->operation, $this->entityId),
            new EntityCapabilityRule($this->subject, $this->table, $this->operation, $this->entityId),
        ];

        foreach ($this->subject->getSubordinates() as $subordinate) {
            $builder = new PolicyBuilder($subordinate, $this->table, $this->operation, $this->entityId);
            $userRules[] = $builder->build();
        }

        $userPolicy = MultiRule::any(...$userRules);

        /** All operations except create imply view */
        if ($this->operation !== Operation::CREATE && $this->operation !== Operation::VIEW) {
            $viewBuilder = new PolicyBuilder($this->subject, $this->table, Operation::VIEW, $this->entityId);
            $viewPolicy = $viewBuilder->build();

            return MultiRule::all($viewPolicy, $userPolicy);
        }

        return $userPolicy;
    }
}
