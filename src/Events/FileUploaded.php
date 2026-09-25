<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after a file has been stored.
 *
 * Dispatched from FileService::storeUpload() once the file is on disk,
 * with the relative path returned by the storage layer.
 */
class FileUploaded
{
    use Dispatchable;

    public function __construct(
        public string $path,
        public string $relType,
        public int $relId,
        public ?int $tenantId = null,
        public string $disk = 'local',
    ) {}
}
