<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Http\ServerRequest;

class AuthorizationContext
{
    /**
     * @var ?\Cake\Http\ServerRequest
     */
    private $request;

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
     * @param ?\Cake\Http\ServerRequest $request The request (if applicable)
     */
    private function __construct(?SubjectInterface $subject, bool $system, ?ServerRequest $request)
    {
        $this->subject = $subject;
        $this->system = $system;
        $this->request = $request;
    }

    /**
     * Creates a new context for the system.
     *
     * @param ?\Cake\Http\ServerRequest $request The request
     * @return \RolesCapabilities\EntityAccess\AuthorizationContext
     */
    public static function asSystem(?ServerRequest $request): AuthorizationContext
    {
        return new AuthorizationContext(null, true, $request);
    }

    /**
     * Creates a new context for the given subject.
     *
     * @param \RolesCapabilities\EntityAccess\SubjectInterface $subject The user
     * @param ?\Cake\Http\ServerRequest $request The request
     * @return \RolesCapabilities\EntityAccess\AuthorizationContext
     */
    public static function asUser(SubjectInterface $subject, ?ServerRequest $request): AuthorizationContext
    {
        return new AuthorizationContext($subject, false, $request);
    }

    /**
     * Creates a new context for anonymous requests
     *
     * @param ?\Cake\Http\ServerRequest $request The request
     * @return \RolesCapabilities\EntityAccess\AuthorizationContext
     */
    public static function asAnonymous(?ServerRequest $request): AuthorizationContext
    {
        return new AuthorizationContext(null, false, $request);
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

    /**
     * @return ?\Cake\Http\ServerRequest The request (if any)
     */
    public function request(): ?ServerRequest
    {
        return $this->request;
    }
}
