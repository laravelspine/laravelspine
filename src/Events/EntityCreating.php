<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired BEFORE an entity is created (Eloquent creating).
 *
 * Generic lifecycle hook — menggantikan pola legacy after/before per entity
 * (after_contract_added, after_estimate_added, ... 100+ hook) menjadi satu set
 * event dengan entityType sebagai parameter.
 *
 * Listener dapat memanggil $event->prevent() untuk MEMBATALKAN operasi.
 */
class EntityCreating
{
    use Dispatchable;

    public bool $prevented = false;

    /**
     * @param class-string $entityType FQCN model (Modules\Sample\Models\SampleItem::class)
     * @param array<string, mixed> $attributes Data yang akan di-create
     */
    public function __construct(
        public string $entityType,
        public array $attributes = [],
    ) {}

    public function prevent(): void
    {
        $this->prevented = true;
    }
}
