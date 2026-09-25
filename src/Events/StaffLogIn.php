<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired AFTER successful staff login.
 *
 * Triggered from AuthController after Sanctum token issued.
 */
class StaffLogIn
{
    use Dispatchable;

    public function __construct(
        public mixed $staff,
        public string $token,
        public array $meta = [],
    ) {}
}