<?php

namespace Spine\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * State dashboard per user (Opsi A — satu baris JSON per user).
 * layout:     {area: [widgetId, ...]} — null = belum diatur (default manifest),
 *             area dengan array kosong = sengaja dikosongkan.
 * visibility: {widgetId: bool} — null = semua widget tampil.
 */
class UserDashboardState extends Model
{
    protected $fillable = ['user_id', 'layout', 'visibility'];

    protected $casts = [
        'layout' => 'array',
        'visibility' => 'array',
    ];
}
