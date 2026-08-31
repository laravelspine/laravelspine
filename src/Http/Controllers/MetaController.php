<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Models\CustomMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom meta API (polymorphic).
 *
 * Meta is key-value data attached to any entity (User, Invoice, ...) via the
 * HasMetaData trait (morphMany to CustomMeta).
 *
 * Because it is polymorphic, the endpoints are generic:
 *   GET    /api/meta/{type}/{id}          -> all meta for an entity
 *   GET    /api/meta/{type}/{id}/{key}    -> one meta value
 *   PUT    /api/meta/{type}/{id}/{key}    -> set/update one meta value
 *   POST   /api/meta/{type}/{id}          -> bulk set (replace)
 *   DELETE /api/meta/{type}/{id}/{key}    -> delete one meta value
 *
 * {type} is a short allowlisted class name (safe, not an arbitrary FQCN).
 *
 * @group api/v1
     * @subgroup Custom Meta
 */
class MetaController extends Controller
{
    /**
     * Map of allowed short type -> FQCN (prevents arbitrary classes).
     * 'user' is NOT hardcoded — it is resolved from the auth config (app-specific).
     *
     * @var array<string,string>
     */
    private const ALLOWED = [];

    private function resolveEntity(string $type, int $id): ?Model
    {
        $type = strtolower($type);
        $fqcn = $type === 'user'
            ? (string) config('auth.providers.users.model')
            : (self::ALLOWED[$type] ?? null);

        if (!$fqcn) {
            return null;
        }

        // Ensure the entity uses the HasMetaData trait
        if (!method_exists($fqcn, 'meta')) {
            return null;
        }

        return $fqcn::find($id);
    }

    /**
     * Get all meta for an entity.
     *
     * @authenticated
     *
     * @urlParam type string required Short entity type (allowlist). Example: user
     * @urlParam id integer required Entity id. Example: 1
     *
     * @response scenario=success {
     *   "data": {"theme": "dark", "language": "id"}
     * }
     */
    public function index(string $type, int $id): JsonResponse
    {
        $entity = $this->resolveEntity($type, $id);

        if (!$entity) {
            return response()->json(['message' => 'Entity not found or type not allowed'], 404);
        }

        return response()->json(['data' => $entity->getMetaArray()]);
    }

    /**
     * Get a single meta value by key.
     *
     * @authenticated
     *
     * @urlParam type string required Short entity type. Example: user
     * @urlParam id integer required Entity id. Example: 1
     * @urlParam key string required Meta key. Example: theme
     *
     * @response scenario=success {
     *   "key": "theme", "value": "dark"
     * }
     * @response status=404 scenario="not found" {
     *   "message": "Meta not found"
     * }
     */
    public function show(string $type, int $id, string $key): JsonResponse
    {
        $entity = $this->resolveEntity($type, $id);

        if (!$entity) {
            return response()->json(['message' => 'Entity not found or type not allowed'], 404);
        }

        if (!$entity->meta()->where('meta_key', $key)->exists()) {
            return response()->json(['message' => 'Meta not found'], 404);
        }

        return response()->json([
            'key' => $key,
            'value' => $entity->getMeta($key),
        ]);
    }

    /**
     * Set or update a single meta value by key.
     *
     * @authenticated
     *
     * @urlParam type string required Short entity type. Example: user
     * @urlParam id integer required Entity id. Example: 1
     * @urlParam key string required Meta key. Example: theme
     * @bodyParam value mixed required Meta value. Example: dark
     *
     * @response scenario=success {
     *   "key": "theme", "value": "dark"
     * }
     */
    public function update(Request $request, string $type, int $id, string $key): JsonResponse
    {
        $entity = $this->resolveEntity($type, $id);

        if (!$entity) {
            return response()->json(['message' => 'Entity not found or type not allowed'], 404);
        }

        $value = $request->input('value');
        $entity->setMeta($key, $value);

        return response()->json([
            'key' => $key,
            'value' => $entity->getMeta($key),
        ]);
    }

    /**
     * Bulk set meta (replace) for an entity.
     *
     * @authenticated
     *
     * @urlParam type string required Short entity type. Example: user
     * @urlParam id integer required Entity id. Example: 1
     * @bodyParam meta object required Map key=>value. Example: {"theme":"dark","language":"id"}
     *
     * @response scenario=success {
     *   "data": {"theme": "dark", "language": "id"}
     * }
     */
    public function store(Request $request, string $type, int $id): JsonResponse
    {
        $entity = $this->resolveEntity($type, $id);

        if (!$entity) {
            return response()->json(['message' => 'Entity not found or type not allowed'], 404);
        }

        $data = (array) $request->input('meta', []);
        $entity->setMetaArray($data);

        return response()->json(['data' => $entity->getMetaArray()]);
    }

    /**
     * Delete a single meta value by key.
     *
     * @authenticated
     *
     * @urlParam type string required Short entity type. Example: user
     * @urlParam id integer required Entity id. Example: 1
     * @urlParam key string required Meta key. Example: theme
     *
     * @response scenario=success {
     *   "message": "Meta deleted"
     * }
     * @response status=404 scenario="not found" {
     *   "message": "Meta not found"
     * }
     */
    public function destroy(string $type, int $id, string $key): JsonResponse
    {
        $entity = $this->resolveEntity($type, $id);

        if (!$entity) {
            return response()->json(['message' => 'Entity not found or type not allowed'], 404);
        }

        if (!$entity->meta()->where('meta_key', $key)->exists()) {
            return response()->json(['message' => 'Meta not found'], 404);
        }

        $entity->deleteMeta($key);

        return response()->json(['message' => 'Meta deleted']);
    }
}
