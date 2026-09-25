<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Models\Attachment;
use Spine\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * File upload API (Laravel Storage + attachment metadata).
 *
 * Uploads store the physical file in storage (per-tenant) and record metadata
 * in the `attachments` table. File access goes through authenticated download
 * routes.
 *
 * Endpoint:
 *   POST   /api/files                  -> upload (multipart)
 *   GET    /api/files/{id}             -> attachment meta
 *   GET    /api/files/{id}/download    -> stream file (force download)
 *   GET    /api/files/{id}/preview     -> stream inline (image/pdf)
 *   DELETE /api/files/{id}             -> delete file + meta
 *   GET    /api/files/limits           -> max upload size (utility)
 *
 * @group api/v1
     * @subgroup Files
 */
class FileController extends Controller
{
    public function __construct(
        private FileService $file
    ) {}

    /**
     * Upload a new file.
     *
     * @authenticated
     *
     * @bodyParam file file required The uploaded file.
     * @bodyParam rel_type string required Entity type (invoice, client, task). Example: invoice
     * @bodyParam rel_id integer required Entity ID. Example: 10
     * @bodyParam tenant_id integer optional Tenant ID (multi-tenant). Example: 1
     * @bodyParam disk string optional 'local' (default, private) | 'public'. Example: local
     *
     * @response scenario=success {
     *   "id": 1, "rel_type": "invoice", "rel_id": 10, "original_name": "doc.pdf",
     *   "mime_type": "application/pdf", "size": 12345, "extension": "pdf"
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:' . (int) (ini_get('upload_max_filesize') ?: 2048)],
            'rel_type' => ['required', 'string', 'max:50'],
            'rel_id' => ['required', 'integer', 'min:1'],
            'tenant_id' => ['nullable', 'integer', 'min:1'],
            'disk' => ['nullable', 'in:local,public'],
        ]);

        /** @var UploadedFile $uploaded */
        $uploaded = $validated['file'];
        $disk = $validated['disk'] ?? 'local';
        $tenantId = $validated['tenant_id'] ?? null;

        $path = $this->file->storeUpload(
            $uploaded,
            $validated['rel_type'],
            (int) $validated['rel_id'],
            isset($validated['tenant_id']) ? (int) $validated['tenant_id'] : null,
            $disk
        );

        $attachment = Attachment::create([
            'rel_type' => $validated['rel_type'],
            'rel_id' => (int) $validated['rel_id'],
            'tenant_id' => $tenantId,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $uploaded->getClientOriginalName(),
            'mime_type' => $uploaded->getClientMimeType(),
            'size' => $uploaded->getSize(),
            'extension' => $uploaded->getClientOriginalExtension(),
        ]);

        return response()->json($attachment, 201);
    }

    /**
     * Get attachment metadata.
     *
     * @authenticated
     *
     * @urlParam id integer required Attachment id. Example: 1
     */
    public function show(int $id): JsonResponse
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        return response()->json($attachment);
    }

    /**
     * Download file (force attachment).
     *
     * @authenticated
     *
     * @urlParam id integer required Attachment id. Example: 1
     */
    public function download(int $id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        return $this->file->downloadResponse($attachment, false);
    }

    /**
     * Preview file inline (image/pdf).
     *
     * @authenticated
     *
     * @urlParam id integer required Attachment id. Example: 1
     */
    public function preview(int $id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        return $this->file->downloadResponse($attachment, true);
    }

    /**
     * Delete an attachment (physical file + metadata).
     *
     * @authenticated
     *
     * @urlParam id integer required Attachment id. Example: 1
     *
     * @response scenario=success {
     *   "message": "Attachment deleted"
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        $this->file->deleteUpload($attachment);

        return response()->json(['message' => 'Attachment deleted']);
    }

    /**
     * Maximum upload size (utility, from php.ini).
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "max_bytes": 2097152, "max_human": "2 MB", "max_post_bytes": 8388608
     * }
     */
    public function limits(): JsonResponse
    {
        $maxBytes = $this->file->file_upload_max_size();

        return response()->json([
            'max_bytes' => $maxBytes,
            'max_human' => $this->file->bytes_to_size($maxBytes),
            'max_post_bytes' => $this->file->parse_size(ini_get('post_max_size') ?: '0'),
        ]);
    }
}
