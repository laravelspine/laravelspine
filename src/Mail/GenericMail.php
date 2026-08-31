<?php

declare(strict_types=1);

namespace Spine\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * GenericMail — a generic Mailable built from an array payload.
 *
 * Used to send emails through the queue (queued email + retry).
 * The payload contains to/subject/view/data.
 */
class GenericMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $subject,
        public string $view,
        public array $data = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subject);
    }

    public function content(): Content
    {
        return new Content(view: $this->view, with: $this->data);
    }
}
