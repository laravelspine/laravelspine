<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired BEFORE cron run starts.
 */
class CronBefore
{
    use Dispatchable;

    public bool $prevented = false;

    public function __construct(
        public array $tasks = [],
    ) {}

    public function prevent(): void
    {
        $this->prevented = true;
    }
}