<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: resolve sales number formatting.
 *
 * Listener returns array:
 *   ['thousands_separator' => '.', 'decimal_point' => ',', 'decimals' => 2]
 */
class SalesNumberFormat
{
    use Dispatchable;

    public ?array $format = null;

    public function __construct(
        public ?int $staffId = null,
    ) {}
}