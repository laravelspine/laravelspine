<?php

declare(strict_types=1);

namespace Modules\Region\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Modules\Region\app\Models\Province;
use Modules\Region\app\Models\Regency;
use Spine\Services\ActivityLogService;

/**
 * CRUD Provinsi & Kabupaten/Kota Indonesia.
 *
 * Provinces: /api/v1/regions/provinces
 * Regencies: /api/v1/regions/regencies?province_id=X
 *          : /api/v1/regions/provinces/{id}/regencies
 */
class RegionController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    /* ──────────────────────────────────────────────────────────────
     * PROVINCES
     * ────────────────────────────────────────────────────────────── */

    public function provinces(): JsonResponse
    {
        return response()->json(['data' => Province::orderBy('name')->get()]);
    }

    public function storeProvince(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:190'],
            'iso_code'    => ['nullable', 'string', 'max:10', 'unique:provinces,iso_code'],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
        ]);

        $province = Province::create($validated);
        Log::info('[Region] province created', ['id' => $province->id, 'name' => $province->name]);

        return response()->json($province, 201);
    }

    public function showProvince(int $id): JsonResponse
    {
        $province = Province::find($id);

        if (! $province) {
            return response()->json(['message' => 'Province not found'], 404);
        }

        return response()->json($province);
    }

    public function updateProvince(int $id, Request $request): JsonResponse
    {
        $province = Province::find($id);

        if (! $province) {
            return response()->json(['message' => 'Province not found'], 404);
        }

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:190'],
            'iso_code'    => ['sometimes', 'string', 'max:10', 'unique:provinces,iso_code,' . $id],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
        ]);

        $province->update($validated);

        Log::info('[Region] province updated', ['id' => $province->id, 'name' => $province->name]);

        return response()->json($province);
    }

    public function destroyProvince(int $id): JsonResponse
    {
        $province = Province::find($id);

        if (! $province) {
            return response()->json(['message' => 'Province not found'], 404);
        }

        $province->delete();

        return response()->json(['message' => 'Province deleted']);
    }

    public function provinceActivityLogs(int $id): JsonResponse
    {
        if (! Province::find($id)) {
            return response()->json(['message' => 'Province not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', Province::class)
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

    /* ──────────────────────────────────────────────────────────────
     * REGENCIES
     * ────────────────────────────────────────────────────────────── */

    public function regencies(Request $request): JsonResponse
    {
        $query = Regency::query();

        if ($request->has('province_id')) {
            $query->where('province_id', (int) $request->query('province_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->query('type')); // 'kota' or 'kabupaten'
        }

        return response()->json(['data' => $query->orderBy('name')->get()]);
    }

    public function provinceRegencies(int $id): JsonResponse
    {
        $province = Province::find($id);

        if (! $province) {
            return response()->json(['message' => 'Province not found'], 404);
        }

        return response()->json(['data' => $province->regencies()->orderBy('name')->get()]);
    }

    public function storeRegency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'name'        => ['required', 'string', 'max:190'],
            'type'        => ['required', 'in:kabupaten,kota'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);

        $regency = Regency::create($validated);
        Log::info('[Region] regency created', ['id' => $regency->id, 'name' => $regency->name]);

        return response()->json($regency, 201);
    }

    public function showRegency(int $id): JsonResponse
    {
        $regency = Regency::find($id);

        if (! $regency) {
            return response()->json(['message' => 'Regency not found'], 404);
        }

        return response()->json($regency);
    }

    public function updateRegency(int $id, Request $request): JsonResponse
    {
        $regency = Regency::find($id);

        if (! $regency) {
            return response()->json(['message' => 'Regency not found'], 404);
        }

        $validated = $request->validate([
            'province_id' => ['sometimes', 'integer', 'exists:provinces,id'],
            'name'        => ['sometimes', 'string', 'max:190'],
            'type'        => ['sometimes', 'in:kabupaten,kota'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);

        $regency->update($validated);

        Log::info('[Region] regency updated', ['id' => $regency->id, 'name' => $regency->name]);

        return response()->json($regency);
    }

    public function destroyRegency(int $id): JsonResponse
    {
        $regency = Regency::find($id);

        if (! $regency) {
            return response()->json(['message' => 'Regency not found'], 404);
        }

        $regency->delete();

        return response()->json(['message' => 'Regency deleted']);
    }

    // ============================================================
    // INERTIA PAGE METHODS
    // ============================================================

    /**
     * Halaman daftar Provinsi (Inertia page).
     */
    public function indexPage(): \Inertia\Response
    {
        // Module enabled check is handled by crm-web routes; this method is kept
        // for reference and potential future direct Inertia rendering from spine.
        return Inertia::render('Region/RegionListPage');
    }

    /**
     * Halaman buat Provinsi baru (Inertia page).
     */
    public function createPage(): \Inertia\Response
    {
        return Inertia::render('Region/RegionCreatePage');
    }

    /**
     * Halaman edit Provinsi (Inertia page).
     */
    public function editPage(int $id): \Inertia\Response
    {
        $province = Province::find($id);
        if (! $province) {
            return Inertia::render('NotFound');
        }
        return Inertia::render('Region/RegionEditPage', ['id' => $id]);
    }
}
