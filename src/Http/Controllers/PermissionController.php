<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spine\Http\Controllers\Concerns\ApiResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use ApiResponse;

    /**
     * List all permissions.
     *
     * @authenticated
     *
     * @queryParam per_page integer Items per page. Example: 15
     *
     * @response {
     *   "data": [
     *     { "id": 1, "name": "users:view", "guard_name": "sanctum" }
     *   ],
     *   "meta": { "count": 1, "per_page": 15, "page": 1 }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');

        $permissions = Permission::where('guard_name', $guard)
            ->paginate((int) $request->query('per_page', 15));

        $data = $permissions->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'guard_name' => $p->guard_name,
        ])->values();

        return $this->list($data, $permissions->total(), $permissions->perPage(), $permissions->currentPage());
    }

    /**
     * Show a single permission.
     *
     * @authenticated
     *
     * @urlParam id integer required Permission ID. Example: 1
     *
     * @response {
     *   "data": { "id": 1, "name": "users:view", "guard_name": "sanctum" }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');
        $permission = Permission::where('guard_name', $guard)->find($id);

        if (! $permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        return $this->ok([
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ]);
    }

    /**
     * Create a new permission.
     *
     * @authenticated
     *
     * @bodyParam name string required Permission name. Example: users:manage
     *
     * @response status=201 {
     *   "data": { "id": 2, "name": "users:manage", "guard_name": "sanctum" }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
        ]);

        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $guard,
        ]);

        return $this->created([
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ]);
    }

    /**
     * Delete a permission.
     *
     * @authenticated
     *
     * @urlParam id integer required Permission ID. Example: 1
     *
     * @response status=204 {}
     */
    public function destroy(int $id): JsonResponse
    {
        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');
        $permission = Permission::where('guard_name', $guard)->find($id);

        if (! $permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        $permission->delete();

        return $this->noContent();
    }
}