<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Spine\Models\Attachment;

/**
 * Fired before an attachment is deleted.
 *
 * Dispatched from FileService::deleteUpload() so listeners can inspect or
 * reject the removal before the physical file is touched — throw a
 * ValidationException to veto (equivalent of a pre-delete guard).
 */
class FileDeleting
{
    use Dispatchable;

    public function __construct(
        public Attachment $attachment,
    ) {}
}
