<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use RolesCapabilities\EntityAccess\AuthorizationContext;

class AuthorizationContextHolder
{
    /**
     * @var AuthorizationContext[]
     */
    private static $ctx = [];

    /**
     * Gets the current state.
     *
     * @return AuthorizationContext[]
     */
    public static function getState(): array
    {
        return self::$ctx;
    }

    /**
     * Sets the current state.
     *
     * @param AuthorizationContext[] $state The state
     * @return void
     */
    public static function setState(array $state): void
    {
        self::$ctx = $state;
    }

    /**
     * Clears the authorization context.
     * Useful for test cases.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$ctx = [];
    }

    /**
     * Pushes a new authorization context.
     *
     * @param AuthorizationContext $ctx The new authorization context
     * @return void
     */
    public static function push(AuthorizationContext $ctx): void
    {
        self::$ctx[] = $ctx;
    }

    /**
     * Pops the authorization context
     * @return ?AuthorizationContext The context if any
     */
    public static function pop(): ?AuthorizationContext
    {
        return array_pop(self::$ctx);
    }

    /**
     * Start performing a system operation (eg logging, authentication etc)
     * IMPORTANT: Make sure you pop after.
     *
     * example:
     * ```
     * AuthorizationContextHolder::asSystem();
     * try {
     * ... Perform privileged operations ...
     * } finally {
     *     AuthorizationContextHolder::pop();
     * }
     * ```
     *
     * @return void
     */
    public static function asSystem(): void
    {
        self::push(AuthorizationContext::asSystem());
    }

    /**
     * Gets the current authorization context
     *
     * @return ?AuthorizationContext
     */
    public static function context(): ?AuthorizationContext
    {
        $len = count(self::$ctx);

        if ($len === 0) {
            return null;
        }

        return self::$ctx[$len - 1];
    }
}
