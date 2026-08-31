<?php

declare(strict_types=1);

namespace Spine\Jobs;

use Spine\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Generate many PDFs and bundle them into a zip in the background.
 *
 * Heavy work must not run inside the HTTP request. The client polls for
 * the result path after the job finishes.
 *
 * Adopted from the legacy `App_bulk_pdf_export` bulk-export helper.
 */
class PdfExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    /**
     * @param  list<array{filename?: string, html: string}>  $documents
     */
    public function __construct(
        public array $documents,
        public string $prefix = 'export'
    ) {}

    public function handle(PdfService $pdf): void
    {
        $result = $pdf->bulkExport([
            'documents' => $this->documents,
            'prefix' => $this->prefix,
        ]);

        // Cache the result so the client can poll for it.
        Cache::put(
            'pdf_export_result_'.md5(json_encode($this->documents)),
            $result,
            now()->addHours(24)
        );
    }
}
