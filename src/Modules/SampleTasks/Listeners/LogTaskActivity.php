<?php

declare(strict_types=1);

namespace Modules\SampleTasks\Listeners;

use Modules\SampleTasks\Models\SampleTask;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

/**
 * CONTOH HOOK — entity lifecycle generic untuk SampleTask (child entity).
 *
 * 1. created/updated/deleted -> activity log (satu listener, semua entity).
 * 2. STATUS-CHANGE PATTERN (task_status_changed): listener EntityUpdated
 *    mengecek changes['status'] — padanan estimate_accepted di legacy.
 *    Log tambahan 'task.status_changed' hanya saat status benar-benar berubah.
 */
class LogTaskActivity
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function created(EntityCreated $event): void
    {
        if (! $event->entity instanceof SampleTask) {
            return;
        }

        $this->activityLog->log(
            "Task created: {$event->entity->title}",
            $event->entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $event->entity instanceof SampleTask) {
            return;
        }

        $this->activityLog->log(
            "Task updated: {$event->entity->title}",
            $event->entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $event->changes],
        );

        // Status-change pattern — padanan task_status_changed legacy:
        // hanya bereaksi saat field 'status' benar-benar berubah.
        $status = $event->changes['status'] ?? null;
        if ($status && $status['old'] !== $status['new']) {
            $this->activityLog->log(
                "Task status changed: {$status['old']} -> {$status['new']}",
                $event->entity,
                $this->user(),
                ['event' => 'task.status_changed', 'old' => $status['old'], 'new' => $status['new']],
            );
        }
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $event->entity instanceof SampleTask) {
            return;
        }

        $this->activityLog->log(
            "Task deleted: {$event->entity->title}",
            null,
            $this->user(),
            ['event' => 'deleted', 'id' => $event->entity->getKey()],
            null,
            $event->entityType,
        );
    }

    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }
}
