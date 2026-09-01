<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Key-value settings API.
 *
 * Not classic CRUD: the key is the identifier, not an auto-increment ID.
 * Supports multi-tenant scope (tenant_id NULL = global).
 *
 * @group api/v1
     * @subgroup Settings
 */
class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly \Nwidart\Modules\Contracts\RepositoryInterface $modules
    ) {}

    /**
     * Settings schema — gabungan manifest semua modul AKTIF.
     *
     * Kontrak frontend untuk halaman Settings: tab (slug/label/icon/position)
     * + fields generic (key/label/type/options/default). Core tidak tahu
     * detail per modul — cukup render apa yang dikirim manifest.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "tabs": [
     *     {"slug":"sample","label":"Sample","icon":"📦","position":51,
     *      "fields":[{"key":"sample_prefix","label":"Prefix","type":"text","default":"SMP"}]}
     *   ]
     * }
     */
    public function schema(): JsonResponse
    {
        // Tab core (Spine) — settings-tabs.php; lalu tab dari manifest modul aktif.
        $tabs = require __DIR__ . '/../../Config/settings-tabs.php';

        foreach ($this->modules->allEnabled() as $module) {
            $manifestFile = $module->getPath() . '/manifest.php';
            if (! is_file($manifestFile)) {
                continue;
            }

            $manifest = require $manifestFile;
            foreach ($manifest['settings'] ?? [] as $tab) {
                $tabs[] = $tab;
            }
        }

        // Urutkan berdasarkan position (padanan position di App_tabs).
        usort($tabs, fn ($a, $b) => ($a['position'] ?? 999) <=> ($b['position'] ?? 999));

        return response()->json(['tabs' => $tabs]);
    }

    /**
     * Get a setting by key.
     *
     * @authenticated
     *
     * @urlParam key string required Setting key. Example: invoice_prefix
     * @queryParam tenant_id integer optional Scope tenant. Null = global. Example: 1
     *
     * @response scenario=success {
     *   "key": "invoice_prefix",
     *   "value": "INV-",
     *   "tenant_id": null
     * }
     * @response status=404 scenario="not found" {
     *   "message": "Setting not found"
     * }
     */
    public function show(Request $request, string $key): JsonResponse
    {
        $tenantId = $request->query('tenant_id') !== null
            ? (int) $request->query('tenant_id') : null;

        if (!$this->settings->has($key, $tenantId)) {
            return response()->json(['message' => 'Setting not found'], 404);
        }

        $value = $this->settings->get($key, null, $tenantId);

        return response()->json([
            'key' => $key,
            'value' => $value,
            'tenant_id' => $tenantId,
        ]);
    }

    /**
     * Create or update a setting (upsert by key).
     *
     * @authenticated
     *
     * @urlParam key string required Setting key. Example: invoice_prefix
     * @bodyParam value string required Setting value. Example: INV-
     * @bodyParam tenant_id integer optional Scope tenant. Null = global. Example: 1
     *
     * @response scenario=success {
     *   "key": "invoice_prefix",
     *   "value": "INV-",
     *   "tenant_id": null
     * }
     */
    public function upsert(Request $request, string $key): JsonResponse
    {
        $value = $request->input('value');
        $tenantId = $request->input('tenant_id') !== null
            ? (int) $request->input('tenant_id') : null;

        $record = $this->settings->set($key, $value, $tenantId);

        return response()->json([
            'key' => $record->key,
            'value' => $record->value,
            'tenant_id' => $record->tenant_id,
        ]);
    }

    /**
     * Delete a setting by key.
     *
     * @authenticated
     *
     * @urlParam key string required Setting key. Example: invoice_prefix
     * @queryParam tenant_id integer optional Scope tenant. Null = global. Example: 1
     *
     * @response scenario=success {
     *   "message": "Setting deleted"
     * }
     * @response status=404 scenario="not found" {
     *   "message": "Setting not found"
     * }
     */
    public function destroy(Request $request, string $key): JsonResponse
    {
        $tenantId = $request->query('tenant_id') !== null
            ? (int) $request->query('tenant_id') : null;

        if (!$this->settings->has($key, $tenantId)) {
            return response()->json(['message' => 'Setting not found'], 404);
        }

        $this->settings->delete($key, $tenantId);

        return response()->json(['message' => 'Setting deleted']);
    }

    /**
     * Get multiple settings at once.
     *
     * @authenticated
     *
     * @bodyParam keys array required List of keys. Example: ["invoice_prefix","tax_rate"]
     * @bodyParam tenant_id integer optional Scope tenant. Null = global. Example: 1
     *
     * @response scenario=success {
     *   "data": {
     *     "invoice_prefix": "INV-",
     *     "tax_rate": "11"
     *   }
     * }
     */
    public function bulk(Request $request): JsonResponse
    {
        $keys = (array) $request->input('keys', []);
        $tenantId = $request->input('tenant_id') !== null
            ? (int) $request->input('tenant_id') : null;

        $out = [];
        foreach ($keys as $k) {
            $out[$k] = $this->settings->get($k, null, $tenantId);
        }

        return response()->json(['data' => $out]);
    }
}
