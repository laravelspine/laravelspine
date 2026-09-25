<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired before an SMTP test email is sent.
 *
 * Dispatched from MailService::testSmtp() before the test mail is sent.
 * The payload array is MUTABLE — listeners may adjust to/subject/body, or
 * throw a ValidationException to abort. Covers the before_send_test_smtp_email
 * action.
 */
class MailTesting
{
    use Dispatchable;

    public function __construct(
        public array $payload,
    ) {}
}
