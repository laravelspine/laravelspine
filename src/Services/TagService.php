<?php

declare(strict_types=1);

namespace Spine\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Tags\Tag;

/**
 * TagService — wrapper tagging.
 *
 * Diadopsi dari `App_tags.php` legacy CRM (get, create, save, relation, all, flat).
 * Implementasi memakai package spatie/laravel-tags (native Laravel).
 *
 */
class TagService
{
    /**
     * Dapatkan/ciptakan tag berdasarkan nama + type.
     */
    public function findOrCreate(string $name, ?string $type = null): Tag
    {
        return Tag::findOrCreate($name, $type);
    }

    /**
     * Ambil semua tag.
     *
     * @return Collection<int, Tag>
     */
    public function all(?string $type = null)
    {
        return $type ? Tag::getWithType($type) : Tag::all();
    }

    /**
     * Tag sebuah model.
     *
     * @param  string|array  $tags
     */
    public function attach(Model $model, $tags, ?string $type = null): void
    {
        $model->attachTag($tags, $type);
    }

    /**
     * Sinkronkan tag model (hapus yang tidak ada di daftar).
     *
     * @param  list<string>  $tags
     */
    public function sync(Model $model, array $tags, ?string $type = null): void
    {
        $model->syncTagsWithType($tags, $type);
    }

    /**
     * Lepas tag dari model.
     *
     * @param  string|array  $tags
     */
    public function detach(Model $model, $tags): void
    {
        $model->detachTag($tags);
    }

    /**
     * Ambil daftar tag milik sebuah model.
     *
     * @return Collection<int, Tag>
     */
    public function tagsOf(Model $model)
    {
        return $model->tags;
    }

    /**
     * Hapus tag secara permanen.
     */
    public function delete(int $id): bool
    {
        $tag = Tag::find($id);

        if (! $tag) {
            return false;
        }

        $tag->delete();

        return true;
    }
}
