<?php

declare(strict_types=1);

namespace Modules\Sample\Listeners;

use Illuminate\Support\Facades\Log;

/**
 * CONTOH HOOK — listener event file Spine.
 *
 * Menunjukkan cara modul memperluas perilaku core TANPA mengubah core:
 * core mendispatch event (FileUploading/FileUploaded/FileDeleting/FileDeleted),
 * modul hanya mendengarkan dan bereaksi (di sini: log + activity).
 */
class LogFileActivity
{
    public function uploading(\Spine\Events\FileUploading $event): void
    {
        Log::info('[Sample] file uploading', [
            'rel_type' => $event->relType,
            'rel_id'   => $event->relId,
            'tenant'   => $event->tenantId,
            'file'     => $event->file->getClientOriginalName(),
        ]);
    }

    public function uploaded(\Spine\Events\FileUploaded $event): void
    {
        Log::info('[Sample] file uploaded', [
            'path'   => $event->path,
            'rel'    => $event->relType . ':' . $event->relId,
            'tenant' => $event->tenantId,
        ]);
    }

    public function deleting(\Spine\Events\FileDeleting $event): void
    {
        Log::info('[Sample] file deleting (veto point)', [
            'path' => $event->attachment->path,
            'rel'  => $event->attachment->rel_type . ':' . $event->attachment->rel_id,
        ]);
    }

    public function deleted(\Spine\Events\FileDeleted $event): void
    {
        Log::info('[Sample] file deleted', [
            'path' => $event->attachment->path,
            'disk' => $event->attachment->disk,
        ]);
    }
}
