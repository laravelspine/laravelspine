<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: resolve option value by key.
 *
 * Listener can return alternative value or null (fallback to DB).
 */
class GetOption
{
    use Dispatchable;

    public ?string $value = null;

    public function __construct(
        public string $key,
        public mixed $default = null,
    ) {}
}