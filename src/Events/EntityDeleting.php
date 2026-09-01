<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Database\Eloquent\Model;

/**
 * Fired BEFORE an entity is deleted (Eloquent deleting).
 *
 * Listener dapat memanggil $event->prevent() untuk MEMBATALKAN operasi.
 */
class EntityDeleting
{
    use Dispatchable;

    public bool $prevented = false;

    public function __construct(
        public string $entityType,
        public Model $entity,
    ) {}

    public function prevent(): void
    {
        $this->prevented = true;
    }
}
