<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: resolve money formatting options.
 *
 * Listener returns array:
 *   ['thousands_separator' => '.', 'decimal_point' => ',', 'currency' => 'IDR', 'symbol' => 'Rp']
 */
class MoneyFormatting
{
    use Dispatchable;

    public ?array $format = null;

    public function __construct(
        public ?int $staffId = null,
    ) {}
}