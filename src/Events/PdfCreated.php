<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after a PDF has been rendered.
 *
 * Dispatched from PdfService::fromHtml() / fromView() once the binary output
 * is produced, carrying the (possibly mutated) payload.
 */
class PdfCreated
{
    use Dispatchable;

    public function __construct(
        public string $binary,
        public array $payload = [],
    ) {}
}
