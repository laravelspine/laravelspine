<?php

declare(strict_types=1);

namespace Spine\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Tags\Tag;

/**
 * TagService — tagging wrapper.
 *
 * Provides get, create, save, relation, all and flat tag operations,
 * backed by the spatie/laravel-tags package (native Laravel).
 */
class TagService
{
    /**
     * Get or create a tag by name + type.
     */
    public function findOrCreate(string $name, ?string $type = null): Tag
    {
        return Tag::findOrCreate($name, $type);
    }

    /**
     * Get all tags.
     *
     * @return Collection<int, Tag>
     */
    public function all(?string $type = null)
    {
        return $type ? Tag::getWithType($type) : Tag::all();
    }

    /**
     * Tag a model.
     *
     * @param  string|array  $tags
     */
    public function attach(Model $model, $tags, ?string $type = null): void
    {
        $model->attachTag($tags, $type);
    }

    /**
     * Sync a model's tags (remove those not in the list).
     *
     * @param  list<string>  $tags
     */
    public function sync(Model $model, array $tags, ?string $type = null): void
    {
        $model->syncTagsWithType($tags, $type);
    }

    /**
     * Detach tags from a model.
     *
     * @param  string|array  $tags
     */
    public function detach(Model $model, $tags): void
    {
        $model->detachTag($tags);
    }

    /**
     * Get the list of tags belonging to a model.
     *
     * @return Collection<int, Tag>
     */
    public function tagsOf(Model $model)
    {
        return $model->tags;
    }

    /**
     * Permanently delete a tag.
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
