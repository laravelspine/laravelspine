<?php

namespace Spine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Metadata for file uploads.
 * The physical file is stored via Laravel Storage; this only holds metadata and a path pointer.
 */
class Attachment extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'rel_type',
        'rel_id',
        'tenant_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'extension',
    ];

    protected $casts = [
        'rel_id' => 'integer',
        'tenant_id' => 'integer',
        'size' => 'integer',
    ];

    /**
     * Polymorphic relation to the owning entity (Invoice, Client, ...).
     * (Optional; the entity may not use the trait. Keep morphTo.)
     */
    public function relatable(): MorphTo
    {
        return $this->morphTo('rel', 'rel_type', 'rel_id');
    }
}
