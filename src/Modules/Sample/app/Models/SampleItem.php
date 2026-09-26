<?php

declare(strict_types=1);

namespace Modules\Sample\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Spine\Traits\HasLifecycleHooks;

class SampleItem extends Model
{
    // Otomatis dispatch EntityCreating/Created/Updating/Updated/Deleting/Deleted
    use HasLifecycleHooks;
    // Auto-generate ULID (bukan UUID) di kolom 'ulid' saat create — bawaan Laravel
    use HasUlids;

    /**
     * Status — source of truth. Konsisten dengan SampleTask (child):
     * parent mengikuti child (semua done -> parent done).
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
    ];

    protected $fillable = ['name', 'description', 'quantity', 'price', 'ulid', 'status'];

    protected $casts = [
        'id'       => 'integer',
        'quantity' => 'integer',
        'price'    => 'decimal:2',
    ];

    /**
     * HasUlids default mengisi PRIMARY KEY — override agar mengisi kolom 'ulid'.
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }
}
