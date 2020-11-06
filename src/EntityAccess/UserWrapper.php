<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Datasource\EntityInterface;
use RolesCapabilities\Access\Utils;

class UserWrapper implements SubjectInterface
{
    /**
     * Wraps a user
     *
     * @param mixed $user The user to wrap
     * @return SubjectInterface
     */
    public static function forUser($user): SubjectInterface
    {
        if ($user instanceof EntityInterface) {
            $user = $user->toArray();
        }

        if (!is_array($user)) {
            error_log(print_r($user, true));
            throw new \RuntimeException('User not an array');
        }

        return new UserWrapper($user);
    }

    /**
     * @var array
     */
    private $user;

    /**
     * @param mixed[] $user The user to wrap
     */
    private function __construct(array $user)
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->user['id'];
    }

    /**
     * Whether the user is a supervisor.
     *
     * @return bool
     */
    private function isSupervisor(): bool
    {
        if (!isset($this->user['is_supervisor'])) {
            return false;
        }

        return (bool)$this->user['is_supervisor'];
    }

    /**
     * @inheritdoc
     */
    public function isSuperuser(): bool
    {
        if (!isset($this->user['is_superuser'])) {
            return false;
        }

        return (bool)$this->user['is_superuser'];
    }

    /**
     * @inheritdoc
     */
    public function getSubordinates(): array
    {
        $subs = [];

        if (!$this->isSupervisor()) {
            return $subs;
        }

        Utils::getReportToUsers($this->getId());

        foreach (Utils::getReportToUsers($this->getId()) as $subordinate) {
            $subs[] = self::forUser($subordinate);
        }

        return $subs;
    }

    /**
     * Unwraps the user
     *
     * @return mixed[] The original user
     */
    public function unwrap(): array
    {
        return $this->user;
    }
}