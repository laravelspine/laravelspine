<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired AFTER a user changes their own password.
 *
 * Triggered from AuthController::updatePassword(), once the new hash is
 * persisted and the other sessions have been revoked.
 *
 * Listeners receive the user and the id of the token that performed the
 * change, so a module can tell "this device rotated its password" apart
 * from "this device was logged out because it was one of the others".
 */
class PasswordChanged
{
    use Dispatchable;

    public function __construct(
        public mixed $user,
        public ?string $tokenId = null,
        public int $revokedSessions = 0,
    ) {}
}
