<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Exceptions\RelationTypeNotRegisteredException;
use Spine\Services\RelationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API resolver relasi antar entity (inti dari relation_helper legacy CRM).
 *
 * Core TIDAK tahu domain; hanya me-resolve tipe yang sudah di-register
 * oleh module via hook (RelationService::registerResolver).
 *
 * Endpoint:
 *   GET /api/relations/{type}/{id}   -> data relasi (opt-in, hanya tipe terdaftar)
 *   GET /api/relations/types         -> list tipe terdaftar
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
     * List tipe relasi yang terdaftar (introspeksi).
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
     * Resolve entity by type + id.
     *
     * Hanya tipe yang sudah di-register (opt-in) yang bisa di-resolve.
     * Tipe tidak terdaftar -> 404.
     *
     * @authenticated
     *
     * @urlParam type string required Tipe relasi terdaftar. Example: user
     * @urlParam id integer required Entity id. Example: 1
     *
     * @response scenario=success {
     *   "id": 1, "type": "user", "name": "Admin", "email": "a@b.c", "exists": true
     * }
     * @response status=404 scenario=tipe tidak terdaftar {
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
