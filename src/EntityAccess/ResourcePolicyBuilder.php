<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use RolesCapabilities\Access\Utils;
use RolesCapabilities\Model\Table\ExtendedCapabilitiesTable;
use RolesCapabilities\Model\Table\PermissionsTable;

class ResourcePolicyBuilder
{
    private $user;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var string
     */
    private $operation;

    /**
     * Creates a new policy builder.
     *
     * @param ?array $user The user
     * @param string $resource The entity class to check ()
     * @param string $operation The operation to perform (one of: list, create, edit, delete)
     */
    public function __construct(?array $user, string $resource, string $operation)
    {
        $this->user = $user;
        $this->resource = $resource;
        $this->operation = $operation;
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
        if (empty($this->user)) {
            return new DenyRule();
        }

        if ($this->isSuperuser()) {
            return new AllowRule();
        }

        $userRules = [
            new ResourceCapabilityRule($this->user['id'], $this->resource, $this->operation),
        ];

        if ($this->isSupervisor()) {
            foreach (Utils::getReportToUsers($this->user['id']) as $subordinate) {
                $builder = new ResourcePolicyBuilder($subordinate, $this->resource, $this->operation);
                $userRules[] = $builder->build();
            }
        }

        return MultiRule::any(...$userRules);
    }
}
