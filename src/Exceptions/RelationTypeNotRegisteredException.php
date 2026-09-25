<?php

declare(strict_types=1);

namespace Spine\Exceptions;

use Exception;

/**
 * Thrown when RelationService::resolve() is called for a type that
 * has not been registered (not yet registered by a module via hook).
 *
 * This is a security guard: the core only resolves opt-in types.
 */
class RelationTypeNotRegisteredException extends Exception
{
    public function __construct(string $type)
    {
        parent::__construct("Relation type '{$type}' is not registered.");
    }
}
