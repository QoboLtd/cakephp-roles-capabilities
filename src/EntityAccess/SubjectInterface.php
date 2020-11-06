<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

interface SubjectInterface
{
    /**
     * Gets the id
     * @return string
     */
    public function getId(): string;

    /**
     * Whether the user is a superuser
     *
     * @return bool
     */
    public function isSuperuser(): bool;

    /**
     * Whether the user is a supervisor.
     *
     * @return bool
     */
    public function isSupervisor(): bool;

    /**
     * @return SubjectInterface[] The subordinates
     */
    public function getSubordinates(): array;
}