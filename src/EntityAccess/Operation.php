<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use ReflectionClass;

abstract class Operation
{
    const CREATE = 'create';
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * Returns all values
     * @return string[] The constants
     */
    public static function values(): array
    {
        $constants = (new ReflectionClass(static::class))->getConstants();

        return array_values($constants);
    }
}
