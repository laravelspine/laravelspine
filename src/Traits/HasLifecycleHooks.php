<?php

declare(strict_types=1);

namespace Spine\Traits;

use Illuminate\Database\Eloquent\Model;
use Spine\Events\EntityCreated;
use Spine\Events\EntityCreating;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityDeleting;
use Spine\Events\EntityUpdated;
use Spine\Events\EntityUpdating;

/**
 * HasLifecycleHooks — otomatis dispatch event lifecycle generic Spine
 * (EntityCreating/Created, EntityUpdating/Updated, EntityDeleting/Deleted)
 * dari Eloquent model events.
 *
 * Pakai di model apa pun: `use HasLifecycleHooks;`
 *
 * Menggantikan pola legacy 100+ hook (after_contract_added, after_estimate_updated,
 * dst) menjadi satu set event dengan entityType + changes (diff old→new).
 *
 * EntityUpdating membawa changes sehingga listener bisa mendeteksi transisi
 * status seperti estimate_accepted / task_status_changed:
 *   $e->changes['status'] = ['old' => 'draft', 'new' => 'accepted']
 *
 * sebelum* (EntityCreating/EntityUpdating/EntityDeleting) bisa di-block:
 *   $event->prevent()
 */
trait HasLifecycleHooks
{
    /**
     * @return array<string, array{old: mixed, new: mixed}> diff field old→new
     */
    protected function lifecycleChanges(): array
    {
        $changes = [];

        foreach ($this->getDirty() as $field => $new) {
            $changes[$field] = [
                'old' => $this->getOriginal($field),
                'new' => $new,
            ];
        }

        return $changes;
    }

    public static function bootHasLifecycleHooks(): void
    {
        static::creating(function (Model $model) {
            $event = new EntityCreating(static::class, $model->getAttributes());
            event($event);

            return ! $event->prevented;
        });

        static::created(function (Model $model) {
            event(new EntityCreated(static::class, $model));
        });

        static::updating(function (Model $model) {
            $event = new EntityUpdating(static::class, $model, $model->lifecycleChanges());
            event($event);

            return ! $event->prevented;
        });

        static::updated(function (Model $model) {
            event(new EntityUpdated(static::class, $model, $model->lifecycleChanges()));
        });

        static::deleting(function (Model $model) {
            $event = new EntityDeleting(static::class, $model);
            event($event);

            return ! $event->prevented;
        });

        static::deleted(function (Model $model) {
            event(new EntityDeleted(static::class, $model));
        });
    }
}
