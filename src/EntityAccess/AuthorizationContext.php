<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Http\ServerRequest;

class AuthorizationContext
{
    /**
     * @var ?\RolesCapabilities\EntityAccess\SubjectInterface
     */
    private $subject;

    /**
     * @var bool
     */
    private $system;

    /**
     * Private constructor. Use one of the asXXX functions.
     *
     * @param ?\RolesCapabilities\EntityAccess\SubjectInterface $subject The subject (ie user)
     * @param bool $system Whether this is a system operation
     */
    private function __construct(?SubjectInterface $subject, bool $system)
    {
        $this->subject = $subject;
        $this->system = $system;
    }

    /**
     * Creates a new context for the system.
     *
     * @return \RolesCapabilities\EntityAccess\AuthorizationContext
     */
    public static function asSystem(): AuthorizationContext
    {
        return new AuthorizationContext(null, true);
    }

    /**
     * Creates a new context for the given subject.
     *
     * @param \RolesCapabilities\EntityAccess\SubjectInterface $subject The user
     * @return \RolesCapabilities\EntityAccess\AuthorizationContext
     */
    public static function asUser(SubjectInterface $subject): AuthorizationContext
    {
        return new AuthorizationContext($subject, false);
    }

    /**
     * Creates a new context for anonymous requests
     *
     * @return \RolesCapabilities\EntityAccess\AuthorizationContext
     */
    public static function asAnonymous(): AuthorizationContext
    {
        return new AuthorizationContext(null, false);
    }

    /**
     * The subject for this context.
     *
     * @return ?\RolesCapabilities\EntityAccess\SubjectInterface The subject or null
     */
    public function subject(): ?SubjectInterface
    {
        return $this->subject;
    }

    /**
     * Whether this is a system operation.
     *
     * @return bool
     */
    public function system(): bool
    {
        return $this->system;
    }
}
