<?php

namespace Spine\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Real-time notification broadcast to a single user.
 *
 * Broadcast to the private channel `user.{id}` with event name
 * `notification.sent` (broadcastAs). The frontend listens on this channel
 * via laravel-echo → Reverb and shows a desktop notification
 * (browser Notification API).
 */
class NotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $userId,
        public string $title,
        public string $message,
        public string $type = 'info',
        public array $data = [],
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'data' => $this->data,
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
