<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after an SMTP test email attempt.
 *
 * Dispatched from MailService::testSmtp() with the outcome, so listeners can
 * log or alert on success/failure. Covers smtp_test_email_success and
 * smtp_test_email_failed.
 */
class MailTested
{
    use Dispatchable;

    public function __construct(
        public bool $success,
        public ?string $error = null,
    ) {}
}
