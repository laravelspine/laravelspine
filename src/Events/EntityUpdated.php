<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Database\Eloquent\Model;

/**
 * Fired AFTER an entity is updated (Eloquent updated).
 *
 * `changes` berisi diff field old→new — pola status-change seperti
 * estimate_accepted / task_status_changed tinggal cek changes['status'].
 */
class EntityUpdated
{
    use Dispatchable;

    /**
     * @param class-string $entityType FQCN model
     * @param array<string, array{old: mixed, new: mixed}> $changes diff field
     */
    public function __construct(
        public string $entityType,
        public Model $entity,
        public array $changes = [],
    ) {}
}
