<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired before an email is sent.
 *
 * Dispatched from MailService::send() before the Mailable is queued/sent.
 * The payload array is MUTABLE — listeners may adjust to/subject/view/data,
 * or throw a ValidationException to abort. Covers the email_template_parsed
 * filter use case.
 */
class MailSending
{
    use Dispatchable;

    public function __construct(
        public array $payload,
    ) {}
}
