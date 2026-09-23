<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired AFTER staff logout (token revoked).
 */
class StaffLogOut
{
    use Dispatchable;

    public function __construct(
        public mixed $staff,
        public array $meta = [],
    ) {}
}