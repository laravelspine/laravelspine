<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Database\Eloquent\Model;

/**
 * Fired BEFORE an entity is updated (Eloquent updating).
 *
 * `changes` berisi diff field old→new (dari Eloquent getDirty vs original),
 * sehingga listener bisa mendeteksi transisi status seperti estimate_accepted:
 * changes['status'] = ['old' => 'draft', 'new' => 'accepted'].
 *
 * Listener dapat memanggil $event->prevent() untuk MEMBATALKAN operasi.
 */
class EntityUpdating
{
    use Dispatchable;

    public bool $prevented = false;

    /**
     * @param class-string $entityType FQCN model
     * @param array<string, array{old: mixed, new: mixed}> $changes diff field
     */
    public function __construct(
        public string $entityType,
        public Model $entity,
        public array $changes = [],
    ) {}

    public function prevent(): void
    {
        $this->prevented = true;
    }
}
