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
        private ModuleService $modules
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