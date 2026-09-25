<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

// Private channel per-user untuk event NotificationSent (broadcastAs
// "notification.sent"). User diverifikasi lewat auth:sanctum — endpoint auth
// didaftarkan SpineServiceProvider::registerBroadcast() (Sanctum) di
// `/api/v1/broadcasting/auth` (app ini API-only; default guard 'web' tak dipakai).
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
