<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Spine\Models\Attachment;

/**
 * Fired after an attachment has been deleted.
 *
 * Dispatched from FileService::deleteUpload() once the physical file is off
 * disk and the metadata row is gone. The Attachment still carries path/disk
 * (id is null after delete), so listeners can log the removal.
 */
class FileDeleted
{
    use Dispatchable;

    public function __construct(
        public Attachment $attachment,
    ) {}
}
