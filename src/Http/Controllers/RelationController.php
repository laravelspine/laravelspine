<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Exceptions\RelationTypeNotRegisteredException;
use Spine\Services\RelationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API resolver for relations between entities.
 *
 * The core does not know the domain; it only resolves types registered
 * by modules via RelationService::registerResolver().
 *
 * Endpoint:
 *   GET /api/relations/{type}/{id}   -> relation data (opt-in, registered types only)
 *   GET /api/relations/types         -> list registered types
 *
 * @group api/v1
     * @subgroup Relations
 */
class RelationController extends Controller
{
    public function __construct(
        private RelationService $relation
    ) {
    }

    /**
     * List registered relation types (introspection).
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "data": ["user", "customer"]
     * }
     */
    public function types(): JsonResponse
    {
        return response()->json(['data' => $this->relation->knownTypes()]);
    }

    /**
     * Resolve an entity by type + id.
     *
     * Only registered (opt-in) types can be resolved.
     * Unregistered types -> 404.
     *
     * @authenticated
     *
     * @urlParam type string required Registered relation type. Example: user
     * @urlParam id integer required Entity id. Example: 1
     *
     * @response scenario=success {
     *   "id": 1, "type": "user", "name": "Admin", "email": "a@b.c", "exists": true
     * }
     * @response status=404 scenario=unregistered type {
     *   "message": "Relation type 'ghost' is not registered."
     * }
     */
    public function show(string $type, int $id): JsonResponse
    {
        try {
            $data = $this->relation->resolve($type, $id);
        } catch (RelationTypeNotRegisteredException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json($data);
    }
}
