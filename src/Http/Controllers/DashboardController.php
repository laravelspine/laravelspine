<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Models\UserDashboardState;
use Spine\Services\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Dashboard widgets — state layout & visibility PER USER.
 *
 * Padanan legacy save_dashboard_widgets_order / _visibility / reset_dashboard:
 * frontend kirim STATE PENUH per drop (bukan diff); area layout bebas
 * (frontend yang define grid-nya); id widget divalidasi terhadap manifest
 * modul aktif (extension registry) — widget siluman ditolak.
 *
 * Kontrak:
 *   GET  /api/v1/dashboard              -> {layout, visibility} (null = default)
 *   PUT  /api/v1/dashboard/order        -> {layout: {area: [id,...]|'empty'}}
 *   PUT  /api/v1/dashboard/visibility   -> {widgets: [{id, visible: 0|1}]}
 *   POST /api/v1/dashboard/reset        -> hapus state -> balik default manifest
 *
 * @group api/v1
 * @subgroup Dashboard
 */
class DashboardController extends Controller
{
    public function __construct(private ModuleService $modules) {}

    /**
     * State dashboard user saat ini.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "data": {
     *     "layout": {"right-4": ["sample-items", "sample-tasks"]},
     *     "visibility": {"sample-items": true, "sample-tasks": false}
     *   }
     * }
     * @response scenario=never-saved {"data": {"layout": null, "visibility": null}}
     */
    public function show(Request $request): JsonResponse
    {
        $state = UserDashboardState::firstOrNew([
            'user_id' => $request->user()->getAuthIdentifier(),
        ]);

        return response()->json([
            'data' => [
                'layout' => $state->layout,
                'visibility' => $state->visibility,
            ],
        ]);
    }

    /**
     * Simpan layout widget (state penuh per drop).
     *
     * @authenticated
     *
     * @bodyParam layout object required Map area -> daftar widget id.
     * Nilai area boleh array id atau string "empty" (area sengaja dikosongkan).
     *
     * @response scenario=success {
     *   "data": {"layout": {"right-4": ["sample-items"]}}
     * }
     * @response status=422 scenario=invalid {"message": "Unknown widget: xxx"}
     */
    public function saveOrder(Request $request): JsonResponse
    {
        $layout = $request->input('layout');
        if (! is_array($layout) || $layout === []) {
            return response()->json(['message' => 'layout wajib berupa map area -> widget id'], 422);
        }

        $registered = array_column($this->modules->widgets(), 'id');
        $clean = [];

        foreach ($layout as $area => $widgets) {
            if (! is_string($area) || trim($area) === '') {
                return response()->json(['message' => 'Nama area tidak valid'], 422);
            }

            // Sentinel 'empty' (padanan legacy) -> array kosong.
            if ($widgets === 'empty') {
                $clean[$area] = [];
                continue;
            }

            if (! is_array($widgets)) {
                return response()->json(['message' => "Isi area '{$area}' harus array widget id atau 'empty'"], 422);
            }

            $ids = [];
            foreach ($widgets as $widgetId) {
                if (! is_string($widgetId) || ! in_array($widgetId, $registered, true)) {
                    return response()->json(['message' => "Unknown widget: {$widgetId}"], 422);
                }
                $ids[] = $widgetId;
            }

            $clean[$area] = array_values(array_unique($ids));
        }

        UserDashboardState::updateOrCreate(
            ['user_id' => $request->user()->getAuthIdentifier()],
            ['layout' => $clean]
        );

        return response()->json(['data' => ['layout' => $clean]]);
    }

    /**
     * Simpan visibility widget (state penuh: semua widget terdaftar, bukan delta).
     *
     * @authenticated
     *
     * @bodyParam widgets array required [{id: string, visible: 0|1}]
     *
     * @response scenario=success {
     *   "data": {"visibility": {"sample-items": true}}
     * }
     */
    public function saveVisibility(Request $request): JsonResponse
    {
        $items = $request->input('widgets');
        if (! is_array($items) || $items === []) {
            return response()->json(['message' => 'widgets wajib berupa array [{id, visible}]'], 422);
        }

        $registered = array_column($this->modules->widgets(), 'id');
        $map = [];

        foreach ($items as $item) {
            $id = $item['id'] ?? null;
            if (! is_string($id) || ! in_array($id, $registered, true)) {
                return response()->json(['message' => "Unknown widget: {$id}"], 422);
            }
            $map[$id] = (bool) ($item['visible'] ?? false);
        }

        UserDashboardState::updateOrCreate(
            ['user_id' => $request->user()->getAuthIdentifier()],
            ['visibility' => $map]
        );

        return response()->json(['data' => ['visibility' => $map]]);
    }

    /**
     * Reset dashboard user ke default (state dihapus -> layout/visibility null).
     *
     * @authenticated
     *
     * @response scenario=success {"message": "Dashboard reset"}
     */
    public function reset(Request $request): JsonResponse
    {
        UserDashboardState::where('user_id', $request->user()->getAuthIdentifier())->delete();

        return response()->json(['message' => 'Dashboard reset']);
    }
}
