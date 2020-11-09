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
     * @return SubjectInterface[] The subordinates
     */
    public function getSubordinates(): array;

    /**
     * @return string[] The groups this user is part of
     */
    public function getGroups(): array;

    /**
     * Gets the roles this user has
     *
     * @return string[] The roles
     */
    public function getRoles(): array;
}
