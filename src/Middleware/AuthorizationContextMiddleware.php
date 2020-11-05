<?php
declare(strict_types=1);

namespace RolesCapabilities\Middleware;

use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use RolesCapabilities\EntityAccess\AuthorizationContext;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;

/**
 * Middleware to integrate with cake Authentication Middleware
 * Add this after Authentication middleware
 */
class AuthorizationContextMiddleware
{

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
        if ($user !== null) {
            $ctx = AuthorizationContext::asUser($user, $request);
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
