<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Http\ServerRequest;

class AuthorizationContext
{
    /**
     * @var ?ServerRequest
     */
    private $request;

    /**
     * @var ?array
     */
    private $subject;

    /**
     * @var bool
     */
    private $system;

    private function __construct(?array $subject, bool $system, ?ServerRequest $request)
    {
        $this->subject = $subject;
        $this->system = $system;
        $this->request = $request;
    }

    /**
     * Creates a new context for the system.
     *
     * @param ?ServerRequest $request The request
     * @return AuthorizationContext
     */
    public static function asSystem(?ServerRequest $request): AuthorizationContext
    {
        return new AuthorizationContext(null, true, $request);
    }

    /**
     * Creates a new context for the given subject.
     *
     * @param mixed[] $subject The user
     * @param ?ServerRequest $request The request
     * @return AuthorizationContext
     */
    public static function asUser(array $subject, ?ServerRequest $request): AuthorizationContext
    {
        return new AuthorizationContext($subject, false, $request);
    }

    /**
     * Creates a new context for anonymous requests
     *
     * @param ?ServerRequest $request The request
     * @return AuthorizationContext
     */
    public static function asAnonymous(?ServerRequest $request): AuthorizationContext
    {
        return new AuthorizationContext(null, false, $request);
    }

    public function subject(): ?array
    {
        return $this->subject;
    }

    public function system(): bool
    {
        return $this->system;
    }

    public function request(): ?ServerRequest
    {
        return $this->request;
    }
}