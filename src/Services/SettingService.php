<?php

declare(strict_types=1);

namespace Spine\Services;

use Spine\Models\Setting;
use Spine\Events\SettingUpdated;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Settings management service.
 *
 * Thin wrapper over the Setting model with tenant scoping.
 *
 */
class SettingService
{
    /**
     * Create or update a setting.
     *
     * @param string $key   Setting name
     * @param mixed  $value Setting value (stored as text)
     * @param int|null $tenantId  Tenant scope (null = global/platform)
     * @return \Spine\Models\Setting
     */
    public function set(string $key, mixed $value, ?int $tenantId = null): Setting
    {
        $existing = $this->resolveQuery($tenantId)->where('key', $key)->first();

        if ($existing) {
            $existing->value = $value;
            $existing->save();
            SettingUpdated::dispatch($existing, false);

            return $existing;
        }

        $setting = Setting::create([
            'key'       => $key,
            'value'     => $value,
            'tenant_id' => $tenantId,
        ]);

        SettingUpdated::dispatch($setting, true);

        return $setting;
    }

    /**
     * Get a setting value, falling back to $default when absent.
     *
     * @param string $key      Setting name
     * @param mixed  $default  Fallback value when absent
     * @param int|null $tenantId Scope (null = global)
     * @return mixed
     */
    public function get(string $key, mixed $default = null, ?int $tenantId = null): mixed
    {
        $record = $this->resolveQuery($tenantId)->where('key', $key)->first();

        if (!$record) {
            return $default;
        }

        return $record->value;
    }

    /**
     * Delete a setting.
     *
     * @param string $key      Setting name
     * @param int|null $tenantId Scope
     * @return bool
     */
    public function delete(string $key, ?int $tenantId = null): bool
    {
        return $this->resolveQuery($tenantId)->where('key', $key)->delete() > 0;
    }

    /**
     * Check whether a setting exists.
     *
     * @param string $key      Setting name
     * @param int|null $tenantId Scope
     * @return bool
     */
    public function has(string $key, ?int $tenantId = null): bool
    {
        return $this->resolveQuery($tenantId)->where('key', $key)->exists();
    }

    /**
     * Get all settings within a given scope.
     *
     * @param int|null $tenantId Scope
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(?int $tenantId = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->resolveQuery($tenantId)->get();
    }

    /**
     * Query builder for a given scope.
     *
     * @param int|null $tenantId Scope (null = no tenant filter)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function resolveQuery(?int $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        $query = Setting::query();

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    /**
     * Alias of get().
     *
     * @param string $key      Setting name
     * @param mixed  $default  Fallback value
     * @param int|null $tenantId Scope
     * @return mixed
     */
    public function get_option(string $key, mixed $default = null, ?int $tenantId = null): mixed
    {
        return $this->get($key, $default, $tenantId);
    }

    /**
     * Get a setting with global -> tenant fallback.
     *
     * Per docs/domain-multitenancy.md: per-tenant settings override global
     * ones. Order: (1) look in the tenant scope, (2) if absent, fall back to
     * global (tenant_id NULL), (3) if still empty, use $default.
     *
     * @param string $key        Setting name
     * @param int|null $tenantId Tenant scope (null = check global only)
     * @param mixed  $default    Final fallback value
     * @return mixed
     */
    public function findWithFallback(string $key, ?int $tenantId = null, mixed $default = null): mixed
    {
        if ($tenantId !== null) {
            $tenantValue = $this->get($key, null, $tenantId);
            if ($tenantValue !== null) {
                return $tenantValue;
            }
        }

        $globalValue = $this->get($key, null, null);
        if ($globalValue !== null) {
            return $globalValue;
        }

        return $default;
    }
}
