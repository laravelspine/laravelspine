<?php

declare(strict_types=1);

namespace Modules\Sample\Listeners;

use Modules\Sample\Models\SampleItem;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

/**
 * CONTOH HOOK #3 — event lifecycle generic Spine (HasLifecycleHooks).
 *
 * Satu listener menangani created/updated/deleted — tapi di-scope ke entity
 * milik modul ini (SampleItem). Modul lain yang listen event sama (mis.
 * SampleTasks::LogTaskActivity) menangani entity-nya sendiri — tidak dobel.
 * entityType + changes (diff old→new) tersedia di event.
 */
class LogEntityActivity
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function created(EntityCreated $event): void
    {
        if (! $event->entity instanceof SampleItem) {
            return;
        }

        $entity = $event->entity;

        $this->activityLog->log(
            "{$event->entityType} created: " . $this->label($entity),
            $entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $event->entity instanceof SampleItem) {
            return;
        }

        $entity = $event->entity;

        $this->activityLog->log(
            "{$event->entityType} updated: " . $this->label($entity),
            $entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $event->changes],
        );
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $event->entity instanceof SampleItem) {
            return;
        }

        $this->activityLog->log(
            "{$event->entityType} deleted: " . $this->label($event->entity),
            null,
            $this->user(),
            ['event' => 'deleted', 'id' => $event->entity->getKey()],
            null,
            $event->entityType,
        );
    }

    /**
     * User pemicu — API pakai guard sanctum, web pakai default.
     */
    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }

    private function label($entity): string
    {
        return (string) ($entity->name ?? $entity->getKey());
    }
}
