<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect allowed upload extensions by type.
 *
 * Listener returns array of extensions (merged).
 */
class UploadAllowedExtensions
{
    use Dispatchable;

    public array $extensions = [];

    public function __construct(
        public string $type,
    ) {}

    public function addExtension(string $ext): void
    {
        $this->extensions[] = $ext;
    }
}