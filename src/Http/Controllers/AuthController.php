<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentication API (Sanctum tokens).
 *
 * @group api/v1
 * @subgroup Auth
 */
class AuthController extends Controller
{
    /**
     * Login with email + password, returns a Sanctum token.
     *
     * @bodyParam email string required User email. Example: demo@spine.test
     * @bodyParam password string required User password. Example: password
     * @bodyParam device_name string optional Token name. Example: web
     *
     * @response scenario=success {
     *   "token": "1|abc...",
     *   "user": {"id": 1, "name": "Demo", "email": "demo@spine.test"}
     * }
     * @response 422 {"message": "The provided credentials are incorrect."}
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:120'],
        ]);

        $userClass = (string) config('auth.providers.users.model');
        $user = $userClass::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Akun nonaktif (kolom is_active opsional per konsumen) ditolak login.
        if (array_key_exists('is_active', $user->getAttributes()) && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account is disabled.'],
            ]);
        }

        $token = $user->createToken($validated['device_name'] ?? 'web')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Register a new user.
     *
     * @bodyParam name string required Display name. Example: New User
     * @bodyParam email string required Unique email. Example: new@spine.test
     * @bodyParam password string required Min 8 chars. Example: secret123
     *
     * @response scenario=success {
     *   "token": "1|abc...",
     *   "user": {"id": 2, "name": "New User", "email": "new@spine.test"}
     * }
     * @response 422 {"message": "The given data was invalid.", "errors": {"email": ["The email has already been taken."]}}
     */
    public function register(Request $request): JsonResponse
    {
        // Kebijakan konsumen: registrasi publik bisa dinonaktifkan via config.
        if (! config('spine.auth.allow_register', true)) {
            return response()->json(['message' => 'Registration is disabled.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:8', 'max:190'],
        ]);

        $userClass = (string) config('auth.providers.users.model');
        if ($userClass::where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken.'],
            ]);
        }

        $user = $userClass::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    /**
     * Logout — revoke the current token.
     *
     * @authenticated
     *
     * @response scenario=success {"message": "Logged out"}
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Current authenticated user.
     *
     * @authenticated
     *
     * @response scenario=success {"id": 1, "name": "Demo", "email": "demo@spine.test"}
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
