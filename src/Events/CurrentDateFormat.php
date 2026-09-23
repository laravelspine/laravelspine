<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: resolve current date format.
 *
 * Listener returns string format (e.g. 'Y-m-d').
 * Return value used by DateService.
 */
class CurrentDateFormat
{
    use Dispatchable;

    public ?string $format = null;

    public function __construct(
        public ?int $staffId = null,
    ) {}
}