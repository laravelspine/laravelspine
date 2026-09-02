<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Services\ModuleService;
use Spine\Events\ModuleActivated;
use Spine\Events\ModuleDeactivated;
use Spine\Events\ModuleInstalled;
use Spine\Events\ModuleUninstalled;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API for module management (discover, status, enable/disable).
 *
 * Only super-admins may enable/disable modules.
 *
 * @group api/v1
     * @subgroup Modules
 */
class ModuleController extends Controller
{
    public function __construct(
        private ModuleService $modules,
        private readonly \Nwidart\Modules\Contracts\RepositoryInterface $moduleRepository
    ) {}

    /**
     * List all modules with their status.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "data": [
     *     {"name":"Sales","alias":"sales","enabled":true,"installed":true,"namespace":"Modules\\Sales"}
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->modules->all()]);
    }

    /**
     * List only enabled modules.
     *
     * @authenticated
     */
    public function enabled(): JsonResponse
    {
        return response()->json(['data' => $this->modules->enabled()]);
    }

    /**
     * Get module details by name/alias.
     *
     * @authenticated
     *
     * @urlParam name string required Module name or alias. Example: sales
     *
     * @response scenario=success {
     *   "name":"Sales","alias":"sales","enabled":true,"installed":true,
     *   "namespace":"Modules\\Sales","providers":[],"aliases":[]
     * }
     * @response status=404 scenario=not-found {"message":"Module not found"}
     */
    public function show(string $name): JsonResponse
    {
        $module = $this->modules->find($name);

        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        return response()->json($module);
    }

    /**
     * Module manifest — kontrak frontend (menu + widgets).
     *
     * Baca manifest.php dari folder modul; fallback ke struktur kosong
     * kalau modul belum menyediakan manifest. Dipakai nextjs-spine untuk
     * render Sidebar & Dashboard tanpa hardcode per modul.
     *
     * @authenticated
     *
     * @urlParam name string required Module name. Example: sample
     *
     * @response scenario=success {
     *   "menu": [{"slug":"sample","label":"Sample","icon":"📦","href":"/sample","position":90}],
     *   "widgets": [{"id":"sample-items","area":"right-4","title":"Sample Items","api":"/api/v1/sample"}]
     * }
     * @response status=404 scenario=not-found {"message":"Module not found"}
     */
    public function manifest(string $name): JsonResponse
    {
        $module = $this->modules->find($name);

        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        // find() mengembalikan array hasil mapModule — 'path' sudah tersedia.
        $manifestFile = $module['path'] . '/manifest.php';
        $manifest = is_file($manifestFile) ? require $manifestFile : [];

        return response()->json([
            'name'    => $module['name'],
            'alias'   => $module['lower'],
            'enabled' => $module['enabled'],
            'menu'    => $manifest['menu'] ?? [],
            'widgets' => $manifest['widgets'] ?? [],
        ]);
    }

    /**
     * Aggregated extensions — menu + widgets dari SEMUA modul aktif.
     *
     * Padanan legacy get_sidebar_menu_items() + render_dashboard_widgets():
     * frontend cukup satu request untuk merender Sidebar & Dashboard.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "menu": [{"slug":"sample","label":"Sample","icon":"📦","href":"/sample","position":90}],
     *   "widgets": [{"id":"sample-items","area":"right-4","title":"Sample Items","api":"/api/v1/sample"}]
     * }
     */
    public function extensions(): JsonResponse
    {
        $menu = [];
        $widgets = [];
        $detailTabs = [];

        foreach ($this->moduleRepository->allEnabled() as $module) {
            $manifestFile = $module->getPath() . '/manifest.php';
            if (! is_file($manifestFile)) {
                continue;
            }

            $manifest = require $manifestFile;
            $lower = strtolower($module->getName());

            foreach ($manifest['menu'] ?? [] as $item) {
                $item['module'] = $module->getName();
                $menu[] = $item;
            }

            foreach ($manifest['widgets'] ?? [] as $widget) {
                $widget['module'] = $module->getName();
                $widgets[] = $widget;
            }

            if (! empty($manifest['detail_tabs'] ?? [])) {
                $detailTabs[$lower] = $manifest['detail_tabs'];
            }
        }

        // HOOK tab lintas modul (padanan add_customer_profile_tab legacy):
        // modul lain bisa menambah tab ke detail modul target via
        // manifest['extend_detail_tabs'][target_lower] = [tab, ...].
        foreach ($this->moduleRepository->allEnabled() as $module) {
            $manifestFile = $module->getPath() . '/manifest.php';
            if (! is_file($manifestFile)) {
                continue;
            }

            $manifest = require $manifestFile;
            foreach ($manifest['extend_detail_tabs'] ?? [] as $target => $tabs) {
                $detailTabs[$target] = array_merge($detailTabs[$target] ?? [], $tabs);
            }
        }

        // Urutkan menu by position (padanan App_menu position).
        usort($menu, fn ($a, $b) => ($a['position'] ?? 999) <=> ($b['position'] ?? 999));

        return response()->json([
            'menu' => $menu,
            'widgets' => $widgets,
            'detail_tabs' => $detailTabs,
        ]);
    }

    /**
     * Enable a module.
     *
     * @authenticated
     *
     * @urlParam name string required Module name. Example: sales
     *
     * @response scenario=success {"message":"Module 'sales' enabled","enabled":true}
     * @response status=404 scenario=not-found {"message":"Module not found"}
     */
    public function enable(string $name): JsonResponse
    {
        $enabled = $this->modules->enable($name);

        if (!$enabled) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        ModuleActivated::dispatch($name);

        return response()->json(['message' => "Module '{$name}' enabled", 'enabled' => true]);
    }

    /**
     * Disable a module.
     *
     * @authenticated
     *
     * @urlParam name string required Module name. Example: sales
     *
     * @response scenario=success {"message":"Module 'sales' disabled","enabled":false}
     * @response status=404 scenario=not-found {"message":"Module not found"}
     */
    public function disable(string $name): JsonResponse
    {
        $disabled = $this->modules->disable($name);

        if (!$disabled) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        ModuleDeactivated::dispatch($name);

        return response()->json(['message' => "Module '{$name}' disabled", 'enabled' => false]);
    }

    /**
     * Check a module's status (enabled/installed).
     *
     * @authenticated
     *
     * @urlParam name string required Module name. Example: sales
     *
     * @response scenario=success {"name":"sales","enabled":true,"installed":true}
     * @response status=404 scenario=not-found {"message":"Module not found"}
     */
    public function status(string $name): JsonResponse
    {
        $module = $this->modules->find($name);

        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        return response()->json([
            'name'      => $module['name'],
            'enabled'   => $module['enabled'],
            'installed' => $module['installed'],
        ]);
    }

    /**
     * Install a new module from a zip upload.
     *
     * The zip must contain `module.json` (at its root or inside a single
     * top-level folder). The module is enabled immediately and its
     * migrations are run.
     *
     * @authenticated
     *
     * @bodyParam file file required Module zip file (max 20MB).
     *
     * @response status=200 scenario=success {
     *   "message": "Module 'Demo' installed",
     *   "data": {"name":"Demo","enabled":true}
     * }
     * @response status=422 scenario=invalid {
     *   "message": "Module zip file is invalid or the module is already installed"
     * }
     */
    public function install(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:zip', 'max:20480'],
        ]);

        $module = $this->modules->installFromZip($request->file('file')->getRealPath());

        if (!$module) {
            return response()->json([
                'message' => 'Module zip file is invalid or the module is already installed',
            ], 422);
        }

        ModuleInstalled::dispatch($module['name'], $module);

        return response()->json([
            'message' => "Module '{$module['name']}' installed",
            'data'    => $module,
        ]);
    }

    /**
     * Uninstall a module: disables it; deletes its files only with ?purge=1.
     *
     * @authenticated
     *
     * @urlParam name string required Module name. Example: demo
     * @queryParam purge boolean Delete the module directory from disk. Example: false
     *
     * @response scenario=success {"message":"Module 'demo' uninstalled","purge":false}
     * @response status=404 scenario=not-found {"message":"Module not found"}
     */
    public function uninstall(string $name, Request $request): JsonResponse
    {
        $purge = filter_var($request->query('purge', false), FILTER_VALIDATE_BOOL);

        $ok = $this->modules->uninstall($name, $purge);

        if (!$ok) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        ModuleUninstalled::dispatch($name, $purge);

        return response()->json([
            'message' => "Module '{$name}' uninstalled",
            'purge'   => $purge,
        ]);
    }
}