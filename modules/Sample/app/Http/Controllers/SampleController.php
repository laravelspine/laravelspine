<?php

declare(strict_types=1);

namespace Modules\Sample\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Modules\Sample\app\Models\SampleItem;
use Spine\Services\ActivityLogService;

/**
 * CONTOH API — CRUD sederhana untuk modul Sample.
 *
 * @group api/v1/sample
 */
class SampleController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    /**
     * Daftar item contoh (API).
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => SampleItem::orderByDesc('id')->get()]);
    }

    /**
     * Buat item contoh (API).
     *
     * @authenticated
     *
     * @bodyParam name string required Nama item. Example: Item A
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'quantity'    => ['nullable', 'integer', 'min:0'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'status'      => ['sometimes', 'string', 'in:draft,in_progress,done'],
        ]);

        $item = SampleItem::create($validated);

        Log::info('[Sample] item created', ['id' => $item->id, 'name' => $item->name]);

        return response()->json($item, 201);
    }

    /**
     * Update item contoh (API).
     *
     * @authenticated
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $item = SampleItem::find($id);

        if (! $item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'quantity'    => ['nullable', 'integer', 'min:0'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'status'      => ['sometimes', 'string', 'in:draft,in_progress,done'],
        ]);

        $item->update($validated);

        Log::info('[Sample] item updated', ['id' => $item->id, 'name' => $item->name]);

        return response()->json($item);
    }

    /**
     * Detail satu item (API).
     *
     * @authenticated
     */
    public function show(int $id): JsonResponse
    {
        $item = SampleItem::find($id);

        if (! $item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    /**
     * Activity log untuk satu item (API).
     *
     * @authenticated
     */
    public function activityLogs(int $id): JsonResponse
    {
        if (! SampleItem::find($id)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', SampleItem::class)
            ->where('subject_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($log) => [
                'id'          => $log->id,
                'description' => $log->description,
                'causer'      => $log->causer?->name ?? 'System',
                'properties'  => $log->properties,
                'at'          => $log->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $logs]);
    }

    /**
     * Hapus item contoh (API).
     *
     * @authenticated
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $item = SampleItem::find($id);

        if (! $item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }

    // ============================================================
    // INERTIA PAGE METHODS
    // ============================================================

    /**
     * Halaman daftar Sample (Inertia page).
     *
     * @authenticated
     */
    public function indexPage(): \Inertia\Response
    {
        return Inertia::render('Sample/SampleListPage');
    }

    /**
     * Halaman create Sample (Inertia page).
     *
     * @authenticated
     */
    public function createPage(): \Inertia\Response
    {
        return Inertia::render('Sample/SampleCreatePage');
    }

    /**
     * Halaman edit Sample (Inertia page).
     *
     * @authenticated
     */
    public function editPage(int $id): \Inertia\Response
    {
        return Inertia::render('Sample/SampleEditPage', ['id' => $id]);
    }

    /**
     * Halaman detail Sample (Inertia page) — FE-03 pattern.
     *
     * @authenticated
     */
    public function detailPage(int $id): \Inertia\Response
    {
        return Inertia::render('Sample/SampleDetailPage', ['id' => $id]);
    }
}
