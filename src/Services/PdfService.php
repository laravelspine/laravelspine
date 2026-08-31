<?php

declare(strict_types=1);

namespace Spine\Services;

use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * PdfService — PDF document generator.
 *
 * Provides:
 *  - Render HTML/Blade → PDF (single)
 *  - Store PDFs to storage (per-tenant)
 *  - Bulk export many documents → a single ZIP file (job/queue for heavy work)
 *
 */
class PdfService
{
    public function __construct(
        private readonly FileService $file
    ) {}

    public function disk(): Filesystem
    {
        return Storage::disk((string) config('pdf.disk', 'local'));
    }

    /**
     * Render an HTML string into a binary PDF.
     *
     * @param  array{html: string, paper?: string, orientation?: string}  $payload
     */
    public function fromHtml(array $payload): string
    {
        $event = new \Spine\Events\PdfCreating($payload);
        event($event);
        $payload = $event->payload;

        $pdf = DomPdf::loadHtml($payload['html'])
            ->setPaper(
                $payload['paper'] ?? config('pdf.defaults.paper', 'a4'),
                $payload['orientation'] ?? config('pdf.defaults.orientation', 'portrait')
            );

        $binary = $pdf->output();

        \Spine\Events\PdfCreated::dispatch($binary, $payload);

        return $binary;
    }

    /**
     * Render a Blade view into a binary PDF.
     *
     * @param  array{view: string, data?: array<string, mixed>, paper?: string, orientation?: string}  $payload
     */
    public function fromView(array $payload): string
    {
        $event = new \Spine\Events\PdfCreating($payload);
        event($event);
        $payload = $event->payload;

        $pdf = DomPdf::loadView(
            $payload['view'],
            $payload['data'] ?? []
        )->setPaper(
            $payload['paper'] ?? config('pdf.defaults.paper', 'a4'),
            $payload['orientation'] ?? config('pdf.defaults.orientation', 'portrait')
        );

        $binary = $pdf->output();

        \Spine\Events\PdfCreated::dispatch($binary, $payload);

        return $binary;
    }

    /**
     * Store a binary PDF to storage and return the relative path.
     *
     * @param  string  $filename  file name (without extension, e.g. 'INV-0001')
     * @param  string  $relType  e.g. 'invoice'
     */
    public function store(string $binary, string $filename, string $relType, int $relId, ?int $tenantId = null): string
    {
        $dir = 'tenants/'.($tenantId ?? 'global').'/'.$relType.'/'.$relId.'/pdf';
        $name = $this->file->sanitize_file_name($filename).'_'.date('Ymd_His').'.pdf';

        $this->disk()->put($dir.'/'.$name, $binary);

        return $dir.'/'.$name;
    }

    /**
     * Build a PDF from a view, store it, and return the path.
     *
     * @param  array<string, mixed>  $payload
     */
    public function generate(array $payload): string
    {
        $view = $payload['view'];
        $data = $payload['data'] ?? [];
        $filename = $payload['filename'] ?? 'document';
        $relType = $payload['rel_type'] ?? 'document';
        $relId = (int) ($payload['rel_id'] ?? 0);
        $tenantId = isset($payload['tenant_id']) ? (int) $payload['tenant_id'] : null;

        $binary = $this->fromView([
            'view' => $view,
            'data' => $data,
            'paper' => $payload['paper'] ?? null,
            'orientation' => $payload['orientation'] ?? null,
        ]);

        return $this->store($binary, $filename, $relType, $relId, $tenantId);
    }

    /**
     * Bulk export: generate many PDFs (from the callback resolver) and zip them.
     *
     * @param  array{documents: list<array{filename: string, html: string}>, prefix?: string}  $payload
     * @return array{path: string, url: string, count: int}
     */
    public function bulkExport(array $payload): array
    {
        $prefix = $this->file->sanitize_file_name($payload['prefix'] ?? 'export');
        $name = $prefix.'_'.date('Ymd_His');
        $tmpDir = storage_path('app/pdf_bulk/'.$name);

        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $count = 0;

        foreach ($payload['documents'] as $doc) {
            $filename = $this->file->sanitize_file_name($doc['filename'] ?? ('doc_'.$count)).'.pdf';
            file_put_contents($tmpDir.'/'.$filename, $this->fromHtml(['html' => $doc['html']]));
            $count++;
        }

        $zipName = $name.'.zip';
        $zipPath = storage_path('app/pdf_bulk/'.$zipName);
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = array_diff(scandir($tmpDir) ?: [], ['.', '..']);
            foreach ($files as $f) {
                $zip->addFile($tmpDir.'/'.$f, $f);
            }
            $zip->close();
        }

        // Clean up the individual PDF files
        $this->deleteDir($tmpDir);

        $disk = $this->disk();
        $disk->put($zipName, file_get_contents($zipPath));
        @unlink($zipPath);

        return [
            'path' => $zipName,
            'url' => $disk->url($zipName),
            'count' => $count,
        ];
    }

    /**
     * Delete a directory recursively.
     */
    private function deleteDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir.'/'.$item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
