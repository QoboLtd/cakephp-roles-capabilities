<?php
declare(strict_types=1);

namespace RolesCapabilities\Middleware;

use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use RolesCapabilities\EntityAccess\AuthorizationContext;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\SubjectInterface;
use RolesCapabilities\EntityAccess\UserWrapper;

/**
 * Middleware that initializes the AuthorizationContext.
 * MUST be added after routing middleware.
 *
 * If your application uses Cake's Authentication Middleware,
 * add this after Authentication middleware.
 */
class AuthorizationContextMiddleware
{
    /**
     * @param mixed $user The user to wrap
     *
     * @return SubjectInterface The wrapped user
     */
    protected function wrapIdentity($user): SubjectInterface
    {
        if ($user instanceof SubjectInterface) {
            return $user;
        }

        return UserWrapper::forUser($user);
    }

    /**
     * Invoke middleware
     *
     * @param ServerRequest $request The request.
     * @param ResponseInterface $response The response.
     * @param callable $next The next middleware to call.
     * @return ResponseInterface A response.
     */
    public function __invoke(ServerRequest $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $user = $request->getAttribute('identity');
        if (!empty($user)) {
            $ctx = AuthorizationContext::asUser($this->wrapIdentity($user), $request);
        } else {
            $ctx = AuthorizationContext::asAnonymous($request);
        }

        AuthorizationContextHolder::push($ctx);
        try {
            return $next($request, $response);
        } finally {
            AuthorizationContextHolder::pop();
        }
    }
}
