<?php

declare(strict_types=1);

namespace Modules\Region\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spine\Traits\HasLifecycleHooks;

class Province extends Model
{
    use HasLifecycleHooks;

    protected $fillable = [
        'name',
        'iso_code',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'id'        => 'integer',
        'latitude'  => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class);
    }
}
