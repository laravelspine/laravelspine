<?php

declare(strict_types=1);

namespace Modules\Sample\Listeners;

use Modules\Sample\Models\SampleItem;
use Modules\SampleTasks\Models\SampleTask;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

/**
 * CONTOH HOOK LINTAS MODUL — parent mengikuti status child.
 *
 * Listen EntityCreated/Updated/Deleted untuk SampleTask (modul SampleTasks)
 * dari dalam modul Sample. Setiap child berubah, hitung ulang status parent:
 *
 *   - SEMUA child done        -> parent status = done
 *   - ada child in_progress   -> parent status = in_progress
 *   - selain itu (semua draft)-> parent status = draft
 *
 * Aturan derivasi bisa disesuaikan nanti (mis. ambang persen, status FINISH,
 * atau mapping lain) — yang penting HOOK berjalan: satu listener, filter
 * entityType/instanceof, tanpa hook khusus per entity (pola legacy 100+ hook).
 */
class SyncSampleStatusFromTasks
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function sync(EntityCreated|EntityUpdated|EntityDeleted $event): void
    {
        if (! $event->entity instanceof SampleTask) {
            return;
        }

        $parent = SampleItem::find($event->entity->sample_item_id);

        if (! $parent) {
            return;
        }

        $statuses = SampleTask::where('sample_item_id', $parent->id)->pluck('status');
        $total = $statuses->count();
        // filter(fn) — Collection::where(satu argumen) salah: 'done' dianggap key
        $done = $statuses->filter(fn ($s) => $s === SampleTask::STATUS_DONE)->count();

        $newStatus = match (true) {
            $total > 0 && $done === $total => SampleItem::STATUS_DONE,
            $done > 0                      => SampleItem::STATUS_IN_PROGRESS,
            default                        => SampleItem::STATUS_DRAFT,
        };

        if ($parent->status === $newStatus) {
            return;
        }

        $old = $parent->status;
        $parent->status = $newStatus;
        $parent->save(); // memicu EntityUpdated SampleItem -> LogEntityActivity

        $this->activityLog->log(
            "Sample #{$parent->id} status synced from tasks: {$old} -> {$newStatus}",
            $parent,
            $this->user(),
            ['event' => 'status.synced_from_tasks', 'old' => $old, 'new' => $newStatus],
        );
    }

    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }
}
