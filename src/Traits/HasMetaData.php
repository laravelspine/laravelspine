<?php

declare(strict_types=1);

namespace Spine\Traits;

use Spine\Models\CustomMeta;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Key-value metadata trait per entity.
 */
trait HasMetaData
{
    /**
     * Get all entity metadata as an array.
     *
     * @return array<string, mixed>
     */
    public function getMetaArray(): array
    {
        return $this->meta()->get()->keyBy('meta_key')
            ->map(fn (CustomMeta $m): mixed => $m->meta_value)
            ->toArray();
    }

    /**
     * Get a single meta value by key.
     *
     * @param string $key
     * @param mixed $default
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        $record = $this->meta()->where('meta_key', $key)->first();

        if (!$record) {
            return $default;
        }

        return $record->meta_value;
    }

    /**
     * Set or update a single meta value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setMeta(string $key, mixed $value): CustomMeta
    {
        $record = $this->meta()->where('meta_key', $key)->first();

        if ($record) {
            $record->meta_value = $value;
            $record->save();
            return $record;
        }

        return $this->meta()->create([
            'meta_key'  => $key,
            'meta_value' => $value,
        ]);
    }

    /**
     * Delete a single meta value by key.
     *
     * @param string $key
     */
    public function deleteMeta(string $key): bool
    {
        return $this->meta()->where('meta_key', $key)->delete() > 0;
    }

    /**
     * Store many meta values at once (replace).
     *
     * @param array<string, mixed> $data
     */
    public function setMetaArray(array $data): void
    {
        // Remove old meta keys not present in the new data
        $currentKeys = $this->meta()->pluck('meta_key')->toArray();
        $newKeys = array_keys($data);

        foreach (array_diff($currentKeys, $newKeys) as $oldKey) {
            $this->deleteMeta($oldKey);
        }

        // Upsert new meta
        foreach ($data as $key => $value) {
            $this->setMeta($key, $value);
        }
    }

    /**
     * Relation to custom meta.
     */
    public function meta(): MorphMany
    {
        return $this->morphMany(CustomMeta::class, 'meta');
    }
}
