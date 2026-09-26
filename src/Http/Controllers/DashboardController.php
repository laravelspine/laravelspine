<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Models\UserDashboardState;
use Spine\Services\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Dashboard widgets — state layout & visibility PER USER.
 *
 * Padanan legacy save_dashboard_widgets_order / _visibility / reset_dashboard:
 * frontend kirim STATE PENUH per drop (bukan diff); area layout bebas
 * (frontend yang define grid-nya); id widget divalidasi terhadap manifest
 * modul aktif (extension registry) + allowlist widget CORE (CORE_WIDGETS) —
 * widget siluman ditolak.
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
    /**
     * Widget dashboard CORE (bukan modul) — selalu ada di dashboard frontend
     * dan ikut dipersist per user: aktivitas terbaru + pautan cepat.
     * Frontend core menempatkannya di area default left-8 / right-4.
     */
    private const CORE_WIDGETS = [
        ['id' => 'activity-log', 'title' => 'Activity Log', 'icon' => '📜', 'api' => '/api/v1/activity', 'area' => 'left-8'],
        ['id' => 'quick-links', 'title' => 'Quick Links', 'icon' => '🔗', 'api' => null, 'area' => 'right-4'],
    ];

    public function __construct(private ModuleService $modules) {}

    /**
     * Seluruh widget id yang boleh disimpan: manifest modul aktif + CORE_WIDGETS.
     *
     * @return list<string>
     */
    private function registeredWidgetIds(): array
    {
        return array_values(array_unique([
            ...array_column($this->modules->widgets(), 'id'),
            ...self::CORE_WIDGETS,
        ]));
    }

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
     * Daftar widget yang tersedia (dengan metadata) yang boleh dilihat oleh user.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "data": {
     *     "widgets": [
     *       {"id": "activity-log", "title": "Activity Log", "icon": "📜", "api": "/api/v1/activity", "area": "left-8", "permission": null},
     *       {"id": "quick-links", "title": "Quick Links", "icon": "🔗", "api": null, "area": "right-4", "permission": null},
     *       {"id": "sample-items", "title": "Sample Items", "icon": "📦", "api": "/api/v1/sample", "area": "right-4", "permission": "view.sample-items"}
     *     ]
     *   }
     * }
     */
    public function widgets(Request $request): JsonResponse
    {
        $user = $request->user();

        // Widget dari core
        $widgets = [];
        foreach (self::CORE_WIDGETS as $widget) {
            // Core widgets tidak memiliki permission secara eksplisit, maka selalu bisa dilihat
            $widgets[] = $widget;
        }

        // Widget dari modul
        foreach ($this->modules->allEnabled() as $module) {
            $manifestFile = $module->getPath() . '/manifest.php';
            if (! is_file($manifestFile)) {
                continue;
            }

            $manifest = require $manifestFile;

            foreach ($manifest['widgets'] ?? [] as $widget) {
                // Cek permission jika ada
                $permission = $widget['permission'] ?? null;
                if ($permission !== null && ! $user->can($permission)) {
                    // User tidak memiliki permission, lewati widget ini
                    continue;
                }

                // Pastikan widget memiliki field yang diperlukan
                $widgets[] = [
                    'id' => $widget['id'],
                    'title' => $widget['title'],
                    'icon' => $widget['icon'] ?? '',
                    'api' => $widget['api'] ?? null,
                    'area' => $widget['area'] ?? 'right-4', // default area
                    'permission' => $permission,
                ];
            }
        }

        return response()->json(['data' => ['widgets' => $widgets]]);
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

        $registered = $this->registeredWidgetIds();
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

        $registered = $this->registeredWidgetIds();
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
