<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $tabs = array_map(
            fn (array $tab): array => $this->withStoredValues($tab),
            require __DIR__ . '/../../Config/settings-tabs.php'
        );

        foreach ($this->modules->allEnabled() as $module) {
            $manifestFile = $module->getPath() . '/manifest.php';
            if (! is_file($manifestFile)) {
                continue;
            }

            $manifest = require $manifestFile;
            foreach ($manifest['settings'] ?? [] as $tab) {
                // Tandai pemilik tab: konsumen (frontend) hanya menampilkan tab
                // untuk modul yang benar-benar ia implementasikan.
                $tabs[] = $this->withStoredValues($tab) + ['module' => $module->getLowerName()];
            }
        }

        // Urutkan berdasarkan position (padanan position di App_tabs).
        usort($tabs, fn ($a, $b) => ($a['position'] ?? 999) <=> ($b['position'] ?? 999));

        return response()->json(['tabs' => $tabs]);
    }

    /**
     * Resolve each field's stored value, falling back to the manifest default.
     *
     * Without this the schema only ever reports `default`, so anything the
     * admin saved through the Settings page is invisible to the frontend and
     * the form silently reverts on reload. `default` is left untouched: it is
     * the fallback shown as a hint, and the reset-to-default affordance.
     *
     * @param  array<string, mixed>  $tab
     * @return array<string, mixed>
     */
    private function withStoredValues(array $tab): array
    {
        $fields = [];

        foreach ($tab['fields'] ?? [] as $field) {
            $key = $field['key'] ?? null;

            $fields[] = $key === null
                ? $field
                : $field + [
                    'value' => $this->settings->get($key, $field['default'] ?? ''),
                ];
        }

        return ['fields' => $fields] + $tab;
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

    /**
     * Bulk create or update settings.
     *
     * Body `values` adalah map key => value; setiap key di-upsert dalam satu
     * scope tenant. Melengkapi `POST /settings/bulk` yang tetap endpoint BACA
     * massal — contract lama tidak diubah.
     *
     * Seluruh penulisan dibungkus satu transaksi: kalau satu key gagal, tidak
     * ada settings yang tersimpan sebagian. Event `SettingUpdated` di-dispatch
     * per key di dalam transaksi, jadi listener yang sudah ter-trigger sebelum
     * rollback tidak bisa ditarik kembali (sifat bawaan SettingService::set()).
     *
     * @authenticated
     *
     * @bodyParam values object required Map key => value. Example: {"companyname":"My Company"}
     * @bodyParam tenant_id integer optional Scope tenant. Null = global. Example: 1
     *
     * @response scenario=success {
     *   "saved": [
     *     {"key":"companyname","value":"My Company","tenant_id":null}
     *   ]
     * }
     * @response status=422 scenario="invalid payload" {
     *   "message":"The values field is required.",
     *   "errors":{"values":["The values field is required."]}
     * }
     */
    public function bulkUpsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'values'    => ['required', 'array', 'min:1'],
            // Nilai harus skalar: array/object akan gagal di-cast kolom `value`.
            'values.*'  => [fn ($attribute, $value, $fail) => is_scalar($value) || is_null($value)
                ?: $fail("The {$attribute} must be a scalar value or null.")],
            'tenant_id' => ['nullable', 'integer'],
        ]);

        $tenantId = $validated['tenant_id'] ?? null;
        $saved = [];

        DB::transaction(function () use ($validated, $tenantId, &$saved): void {
            foreach ($validated['values'] as $key => $value) {
                $record = $this->settings->set((string) $key, $this->normalizeValue($value), $tenantId);

                $saved[] = [
                    'key'       => $record->key,
                    'value'     => $record->value,
                    'tenant_id' => $record->tenant_id,
                ];
            }
        });

        return response()->json(['saved' => $saved]);
    }

    /**
     * Normalize a submitted value into the text form stored in `settings.value`.
     *
     * Checkbox dikirim sebagai boolean dan kolom `value` di-cast string, sehingga
     * `false` tersimpan sebagai "" — tidak konsisten dengan default schema yang
     * memakai "1"/"0". Jadi boolean dinormalkan ke "1"/"0".
     *
     * @param mixed $value Raw value as submitted
     * @return string|null Value ready for storage (null stays null)
     */
    protected function normalizeValue(mixed $value): ?string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_null($value)) {
            return null;
        }

        return (string) $value;
    }

    /**
     * Profile settings schema — gabungan core profile tabs + module profile_tabs.
     *
     * Mirrors schema() tapi untuk halaman Profile (per-user settings).
     * Setiap user dapat mengakses profile settings-nya sendiri.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "tabs": [
     *     {"slug":"account","label":"Account","icon":"👤","position":5,
     *      "fields":[{"key":"name","label":"Full Name","type":"text"}]}
     *   ]
     * }
     */
    public function profileSchema(): JsonResponse
    {
        $tabs = require __DIR__ . '/../../Config/profile-tabs.php';

        foreach ($this->modules->allEnabled() as $module) {
            $manifestFile = $module->getPath() . '/manifest.php';
            if (! is_file($manifestFile)) {
                continue;
            }

            $manifest = require $manifestFile;
            foreach ($manifest['profile_tabs'] ?? [] as $tab) {
                $tabs[] = $tab + ['module' => $module->getLowerName()];
            }
        }

        usort($tabs, fn ($a, $b) => ($a['position'] ?? 999) <=> ($b['position'] ?? 999));

        return response()->json(['tabs' => $tabs]);
    }
}
