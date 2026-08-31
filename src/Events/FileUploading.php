<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\UploadedFile;

/**
 * Fired before a file is stored.
 *
 * Dispatched from FileService::storeUpload() so listeners can inspect or
 * reject the upload before it hits storage — throw a ValidationException
 * to veto (equivalent of a pre-upload guard).
 */
class FileUploading
{
    use Dispatchable;

    public function __construct(
        public UploadedFile $file,
        public string $relType,
        public int $relId,
        public ?int $tenantId = null,
        public string $disk = 'local',
    ) {}
}
