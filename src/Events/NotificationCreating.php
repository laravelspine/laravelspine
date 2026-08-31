<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired before a realtime notification is broadcast.
 *
 * Dispatched from BroadcastController::sendTest() before NotificationSent.
 * The payload array is MUTABLE — listeners may adjust title/message/type/data,
 * or throw a ValidationException to abort the broadcast. Covers the
 * notification_data filter use case.
 */
class NotificationCreating
{
    use Dispatchable;

    public function __construct(
        public array $payload,
    ) {}
}
