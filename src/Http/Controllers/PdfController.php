<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Http\Controllers\Concerns\ApiResponse;
use Spine\Jobs\PdfExportJob;
use Spine\Services\PdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PDF document generator API.
 *
 * Supports single synchronous generation and bulk export via a queued job.
 *
 * Endpoint:
 *   POST /api/pdf/generate         -> generate one PDF from a view, store to storage
 *   POST /api/pdf/from-html        -> render an HTML string into a PDF
 *   POST /api/pdf/bulk-export      -> dispatch a bulk PDF job (background)
 *
 * @group api/v1
     * @subgroup Pdf
 */
class PdfController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PdfService $pdf
    ) {}

    /**
     * Generate a single PDF from a Blade view and store it in storage.
     *
     * @authenticated
     *
     * @bodyParam view string required Blade view name. Example: pdf.invoice
     * @bodyParam data array Data passed to the view.
     * @bodyParam filename string Filename (without extension). Example: INV-0001
     * @bodyParam rel_type string Entity type (invoice, estimate, contract). Example: invoice
     * @bodyParam rel_id integer Entity ID. Example: 10
     * @bodyParam tenant_id integer optional Tenant ID. Example: 1
     * @bodyParam paper string Paper size (a4, letter). Example: a4
     * @bodyParam orientation string portrait|landscape. Example: portrait
     *
     * @response {
     *   "data": { "path": "tenants/1/invoice/10/pdf/INV-0001_20260828_123456.pdf" }
     * }
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'view' => ['required', 'string'],
            'data' => ['nullable', 'array'],
            'filename' => ['nullable', 'string', 'max:120'],
            'rel_type' => ['nullable', 'string', 'max:50'],
            'rel_id' => ['nullable', 'integer', 'min:0'],
            'tenant_id' => ['nullable', 'integer', 'min:1'],
            'paper' => ['nullable', 'string', 'max:20'],
            'orientation' => ['nullable', 'in:portrait,landscape'],
        ]);

        $path = $this->pdf->generate($validated);

        return $this->created(['path' => $path]);
    }

    /**
     * Render an HTML string into a PDF (binary, without storing).
     *
     * @authenticated
     *
     * @bodyParam html string required HTML content.
     * @bodyParam paper string Paper size. Example: a4
     * @bodyParam orientation string portrait|landscape. Example: portrait
     *
     * @response data: "string" (binary PDF)
     */
    public function fromHtml(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'html' => ['required', 'string'],
            'paper' => ['nullable', 'string', 'max:20'],
            'orientation' => ['nullable', 'in:portrait,landscape'],
        ]);

        $binary = $this->pdf->fromHtml($validated);

        return $this->ok(base64_encode($binary));
    }

    /**
     * Bulk-export multiple PDF documents into a single ZIP (run via the queue).
     *
     * @authenticated
     *
     * @bodyParam documents array required List of documents.
     * @bodyParam documents[].filename string Filename for each document. Example: INV-0001
     * @bodyParam documents[].html string required Document HTML. Example: <h1>Invoice</h1>
     * @bodyParam prefix string ZIP filename prefix. Example: invoices-2026
     *
     * @response {
     *   "data": { "job": "Spine\\Jobs\\PdfExportJob", "queued": true }
     * }
     */
    public function bulkExport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*.filename' => ['nullable', 'string', 'max:120'],
            'documents.*.html' => ['required', 'string'],
            'prefix' => ['nullable', 'string', 'max:120'],
        ]);

        PdfExportJob::dispatch(
            $validated['documents'],
            $validated['prefix'] ?? 'export'
        );

        return $this->accepted([
            'job' => PdfExportJob::class,
            'queued' => true,
        ]);
    }

    protected function accepted(mixed $data = null): JsonResponse
    {
        return response()->json(['data' => $data], 202);
    }
}
