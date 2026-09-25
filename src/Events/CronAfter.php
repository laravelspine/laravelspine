<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired AFTER cron run completes.
 */
class CronAfter
{
    use Dispatchable;

    public function __construct(
        public array $results = [],
    ) {}
}