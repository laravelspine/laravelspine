<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired before a PDF is rendered.
 *
 * Dispatched from PdfService::fromHtml() / fromView(). The payload array is
 * MUTABLE — listeners may adjust html/view/data/paper/orientation before the
 * render, or throw a ValidationException to abort. Covers the PDF data-filter
 * use cases (payload formatting, signature line breaks, table separators).
 */
class PdfCreating
{
    use Dispatchable;

    public function __construct(
        public array $payload,
    ) {}
}
