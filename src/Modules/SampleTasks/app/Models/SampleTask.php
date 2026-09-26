<?php

declare(strict_types=1);

namespace Modules\SampleTasks\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Sample\Models\SampleItem;
use Spine\Traits\HasLifecycleHooks;

/**
 * SampleTask — CHILD entity dari SampleItem (belongsTo).
 *
 * Menguji entity lifecycle hooks lintas modul:
 *   - EntityCreated/Updated/Deleted otomatis (HasLifecycleHooks)
 *   - status-change pattern (task_status_changed): EntityUpdated dengan
 *     changes['status'] — listener bisa bereaksi seperti estimate_accepted.
 */
class SampleTask extends Model
{
    use HasLifecycleHooks;
    use HasUlids;

    /**
     * Definisi status — source of truth (dipakai UI "Mark as", hook parent,
     * validasi). Padanan enum legacy: pending/in_progress/done.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING      => 'Pending',
        self::STATUS_IN_PROGRESS  => 'In Progress',
        self::STATUS_DONE         => 'Done',
    ];

    protected $fillable = ['sample_item_id', 'title', 'status', 'ulid'];

    protected $casts = [
        'id'             => 'integer',
        'sample_item_id' => 'integer',
    ];

    /**
     * HasUlids default mengisi PRIMARY KEY — override agar mengisi kolom 'ulid'.
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function sampleItem(): BelongsTo
    {
        return $this->belongsTo(SampleItem::class);
    }
}
