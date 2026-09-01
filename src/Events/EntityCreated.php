<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Database\Eloquent\Model;

/**
 * Fired AFTER an entity is created (Eloquent created).
 *
 * Generic lifecycle hook — menggantikan after_contract_added, after_estimate_added,
 * dst (100+ hook legacy) menjadi satu event dengan entityType sebagai parameter.
 */
class EntityCreated
{
    use Dispatchable;

    public function __construct(
        public string $entityType,
        public Model $entity,
    ) {}
}
