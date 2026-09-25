<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Database\Eloquent\Model;

/**
 * Fired AFTER an entity is deleted (Eloquent deleted).
 */
class EntityDeleted
{
    use Dispatchable;

    public function __construct(
        public string $entityType,
        public Model $entity,
    ) {}
}
