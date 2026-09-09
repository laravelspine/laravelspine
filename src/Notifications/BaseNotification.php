<?php

declare(strict_types=1);

namespace Spine\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Base notification untuk bell dashboard.
 *
 * Modul extends class ini lalu mengirim via
 * `Notification::send($users, new XxxNotification(...))`.
 *
 * HOOK channel = override `via()`: tambah 'mail', 'broadcast' (pusher),
 * 'telegram', dll — infra tidak berubah, shape `toDatabase()` tetap.
 * Shape database: title, body, module, url (opsional, target halaman),
 * data (opsional, per-modul).
 */
abstract class BaseNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $body,
        public string $module = '',
        public ?string $url = null,
        public array $data = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'module' => $this->module,
            'url' => $this->url,
            'data' => $this->data,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
