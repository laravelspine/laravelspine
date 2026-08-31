<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Http\Controllers\Concerns\ApiResponse;
use Spine\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tagging API (spatie/laravel-tags).
 *
 * Global tag management + attach/detach on taggable models.
 *
 * Endpoint:
 *   GET  /api/tags              -> list all tags (optional ?type=)
 *   POST /api/tags              -> create a tag
 *   DELETE /api/tags/{id}       -> delete a tag
 *
 * @group api/v1
     * @subgroup Tags
 */
class TagController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TagService $tags
    ) {}

    /**
     * List all tags.
     *
     * @authenticated
     *
     * @queryParam type string Filter by tag type. Example: invoice
     *
     * @response {
     *   "data": [ { "id": 1, "name": "penting" } ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');

        $tags = $this->tags->all($type ?: null)->map(fn (object $tag) => [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'type' => $tag->type,
        ])->values();

        return $this->list($tags, $tags->count(), $tags->count(), 1);
    }

    /**
     * Create a new tag.
     *
     * @authenticated
     *
     * @bodyParam name string required Tag name. Example: prioritas-tinggi
     * @bodyParam type string optional Tag type. Example: invoice
     *
     * @response status=201 {
     *   "data": { "id": 1, "name": "prioritas-tinggi" }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:50'],
        ]);

        $tag = $this->tags->findOrCreate($validated['name'], $validated['type'] ?? null);

        return $this->created([
            'id' => $tag->id,
            'name' => $tag->name,
            'type' => $tag->type,
        ]);
    }

    /**
     * Delete a tag.
     *
     * @authenticated
     *
     * @urlParam id integer required Tag ID. Example: 1
     *
     * @response scenario=success {
     *   "message": "Tag deleted"
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->tags->delete($id);

        if (! $deleted) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        return response()->json(['message' => 'Tag deleted']);
    }
}
