<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when a relation is being resolved.
 *
 * Dispatched from RelationService::resolve() before the resolver result is
 * returned. The payload array is MUTABLE — listeners may adjust the resolved
 * data, or throw a ValidationException to abort. Covers the relation data
 * filter use cases (get_relation_data, relation_values).
 */
class RelationResolving
{
    use Dispatchable;

    public function __construct(
        public array $payload,
    ) {}
}
