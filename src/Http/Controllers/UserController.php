<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spine\Http\Controllers\Concerns\ApiResponse;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * List all users.
     *
     * @authenticated
     *
     * @queryParam per_page integer Items per page. Example: 15
     *
     * @response {
     *   "data": [
     *     { "id": 1, "name": "Demo", "email": "demo@spine.test", "roles": ["admin"] }
     *   ],
     *   "meta": { "count": 1, "per_page": 15, "page": 1 }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $userModel = config('auth.providers.users.model');
        $users = $userModel::with('roles')
            ->select(['id', 'name', 'email'])
            ->paginate((int) $request->query('per_page', 15));

        $data = $users->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'roles' => $u->getRoleNames(),
        ])->values();

        return $this->list($data, $users->total(), $users->perPage(), $users->currentPage());
    }

    /**
     * Show a single user.
     *
     * @authenticated
     *
     * @urlParam id integer required User ID. Example: 1
     *
     * @response {
     *   "data": { "id": 1, "name": "Demo", "email": "demo@spine.test", "roles": ["admin"] }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $userModel = config('auth.providers.users.model');
        $user = $userModel::with('roles')->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return $this->ok([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * Create a new user.
     *
     * @authenticated
     *
     * @bodyParam name string required Display name. Example: New User
     * @bodyParam email string required Email. Example: new@spine.test
     * @bodyParam password string required Password (min 8 chars). Example: secret123
     * @bodyParam roles array optional List of role names. Example: ["admin"]
     *
     * @response status=201 {
     *   "data": { "id": 2, "name": "New User", "email": "new@spine.test", "roles": ["admin"] }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:190'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
        ]);

        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Hash::make($validated['password']),
        ]);

        if (! empty($validated['roles'] ?? [])) {
            $user->assignRole($validated['roles']);
        }

        $user->load('roles');

        return $this->created([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * Update an existing user.
     *
     * @authenticated
     *
     * @urlParam id integer required User ID. Example: 1
     * @bodyParam name string optional Display name.
     * @bodyParam email string optional Email.
     * @bodyParam password string optional Password (min 8 chars).
     * @bodyParam roles array optional List of role names (syncs).
     *
     * @response {
     *   "data": { "id": 1, "name": "Updated", "email": "demo@spine.test", "roles": ["admin"] }
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $userModel = config('auth.providers.users.model');
        $user = $userModel::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:190'],
            'email' => ['sometimes', 'email', 'max:190', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:8', 'max:190'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $user->password = \Hash::make($validated['password']);
        }

        $user->save();

        if (array_key_exists('roles', $validated)) {
            $user->syncRoles($validated['roles'] ?? []);
        }

        $user->load('roles');

        return $this->ok([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * Delete a user.
     *
     * @authenticated
     *
     * @urlParam id integer required User ID. Example: 1
     *
     * @response status=204 {}
     */
    public function destroy(int $id): JsonResponse
    {
        $userModel = config('auth.providers.users.model');
        $user = $userModel::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return $this->noContent();
    }
}