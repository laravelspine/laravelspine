<?php

declare(strict_types=1);

namespace Spine\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * GenericMailNotification — a simple email notification.
 *
 * Used by MailService to send email notifications to users.
 */
class GenericMailNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private string $subject,
        private string $body,
        private ?string $actionUrl = null,
        private array $payload = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->subject)
            ->line($this->body);

        if ($this->actionUrl) {
            $message->action('Lihat Detail', $this->actionUrl);
        }

        return $message;
    }
}
