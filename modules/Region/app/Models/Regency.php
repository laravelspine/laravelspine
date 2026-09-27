<?php

declare(strict_types=1);

namespace Modules\Region\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spine\Traits\HasLifecycleHooks;

class Regency extends Model
{
    use HasLifecycleHooks;

    protected $fillable = [
        'province_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'id'        => 'integer',
        'province_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
