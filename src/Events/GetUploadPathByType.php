<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: resolve upload path by type.
 *
 * Listener returns string path or null (fallback to default).
 */
class GetUploadPathByType
{
    use Dispatchable;

    public ?string $path = null;

    public function __construct(
        public string $type,
        public ?int $relationId = null,
    ) {}
}