<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use InvalidArgumentException;
use ReflectionClass;

abstract class Operation
{
    const CREATE = 'create';
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    private static $aliases = [
        'list' => VIEW,
        'index' => VIEW,
        'add' => CREATE,
    ];

    /**
     * Returns all values
     * @return string[] The constants
     */
    public static function values(): array
    {
        $constants = (new ReflectionClass(static::class))->getConstants();

        return array_values($constants);
    }

    /**
     * Returns the standard operation for the given value
     *
     * @param string $value The operation name or alias
     * @return ?string The operation or null if not recognised
     */
    public static function value(string $value): ?string
    {
        $values = self::values();

        if (in_array($value, $values)) {
            return $value;
        }

        if (isset(self::$aliases[$value])) {
            return self::$aliases[$value];
        }

        return null;
    }
}
