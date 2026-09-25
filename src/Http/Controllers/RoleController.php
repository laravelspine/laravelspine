<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spine\Http\Controllers\Concerns\ApiResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * List all roles.
     *
     * @authenticated
     *
     * @queryParam per_page integer Items per page. Example: 15
     *
     * @response {
     *   "data": [
     *     { "id": 1, "name": "admin", "guard_name": "sanctum", "permissions": ["users:view", "users:manage"] }
     *   ],
     *   "meta": { "count": 1, "per_page": 15, "page": 1 }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');

        $roles = Role::where('guard_name', $guard)
            ->with('permissions')
            ->paginate((int) $request->query('per_page', 15));

        $data = $roles->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'guard_name' => $r->guard_name,
            'permissions' => $r->permissions->pluck('name'),
        ])->values();

        return $this->list($data, $roles->total(), $roles->perPage(), $roles->currentPage());
    }

    /**
     * Show a single role.
     *
     * @authenticated
     *
     * @urlParam id integer required Role ID. Example: 1
     *
     * @response {
     *   "data": { "id": 1, "name": "admin", "guard_name": "sanctum", "permissions": ["users:view"] }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');
        $role = Role::where('guard_name', $guard)->with('permissions')->find($id);

        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return $this->ok([
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Create a new role.
     *
     * @authenticated
     *
     * @bodyParam name string required Role name. Example: editor
     * @bodyParam permissions array optional List of permission names. Example: ["users:view"]
     *
     * @response status=201 {
     *   "data": { "id": 2, "name": "editor", "guard_name": "sanctum", "permissions": ["users:view"] }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $guard,
        ]);

        if (! empty($validated['permissions'] ?? [])) {
            $role->syncPermissions($validated['permissions']);
        }

        $role->load('permissions');

        return $this->created([
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Update an existing role.
     *
     * @authenticated
     *
     * @urlParam id integer required Role ID. Example: 1
     * @bodyParam name string optional New role name.
     * @bodyParam permissions array optional List of permission names (syncs).
     *
     * @response {
     *   "data": { "id": 1, "name": "admin", "guard_name": "sanctum", "permissions": ["users:manage"] }
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');
        $role = Role::where('guard_name', $guard)->find($id);

        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:190', 'unique:roles,name,' . $id . ',id,guard_name,' . $guard],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        if (isset($validated['name'])) {
            $role->name = $validated['name'];
        }

        $role->save();

        if (array_key_exists('permissions', $validated)) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        $role->load('permissions');

        return $this->ok([
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Delete a role.
     *
     * @authenticated
     *
     * @urlParam id integer required Role ID. Example: 1
     *
     * @response status=204 {}
     */
    public function destroy(int $id): JsonResponse
    {
        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');
        $role = Role::where('guard_name', $guard)->find($id);

        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $role->delete();

        return $this->noContent();
    }
}