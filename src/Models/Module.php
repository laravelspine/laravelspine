<?php

declare(strict_types=1);

namespace Spine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Timestamps;

/**
 * @property int $id
 * @property string $name
 * @property string|null $namespace
 * @property bool $enabled
 * @property bool $installed
 * @property string|null $description
 * @property int $priority
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Module extends Model
{
    protected $fillable = [
        'name',
        'namespace',
        'enabled',
        'installed',
        'description',
        'priority',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'installed' => 'boolean',
        'priority' => 'integer',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeInstalled($query)
    {
        return $query->where('installed', true);
    }
}
