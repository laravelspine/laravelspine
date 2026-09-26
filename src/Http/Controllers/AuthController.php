<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Spine\Services\TwoFactorService;

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
     * If 2FA is enabled on the account, returns a challenge instead of a token.
     *
     * @bodyParam email string required User email. Example: demo@spine.test
     * @bodyParam password string required User password. Example: password
     * @bodyParam device_name string optional Token name. Example: web
     *
     * @response scenario=success {
     *   "token": "1|abc...",
     *   "user": {"id": 1, "name": "Demo", "email": "demo@spine.test"}
     * }
     * @response scenario=2fa_required {
     *   "2fa_required": true,
     *   "2fa_methods": ["totp","email"],
     *   "challenge_id": "uuid-here"
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

        // The password was just proven correct, so this is the one safe moment
        // to upgrade a stale hash. Hash::check() reads the algorithm from the
        // stored string, so an account still on bcrypt keeps working after the
        // driver moves to argon2id -- it is silently re-hashed here instead.
        // That is what makes the switch non-breaking: no forced re-login, no
        // bulk UPDATE over the users table, and no window where credentials
        // hashed with the old algorithm stop verifying.
        if (Hash::needsRehash($user->password)) {
            $user->password = $validated['password'];
            $user->save();
        }

        // Akun nonaktif (kolom is_active opsional per konsumen) ditolak login.
        if (array_key_exists('is_active', $user->getAttributes()) && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account is disabled.'],
            ]);
        }

        $twoFactor = app(TwoFactorService::class);
        $status = $twoFactor->status($user);

        if ($status['enabled']) {
            $challengeId = (string) \Illuminate\Support\Str::uuid();
            Cache::put("2fa_challenge:{$challengeId}", [
                'user_id' => $user->id,
                'created_at' => now()->toDateTimeString(),
            ], now()->addMinutes(10));

            return response()->json([
                '2fa_required' => true,
                '2fa_methods' => $status['methods'],
                'challenge_id' => $challengeId,
            ]);
        }

        $token = $user->createToken($validated['device_name'] ?? 'web')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
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
            'user' => $this->formatUser($user),
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
     * @response scenario=success {
     *   "id": 1, "name": "Demo", "email": "demo@spine.test", "ulid": "01H...",
     *   "language": "en", "is_admin": false,
     *   "permissions": ["staff.view_own"], "roles": ["employee"]
     * }
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json($this->formatUser($user));
    }

    /**
     * Update current user profile (name, email, language).
     *
     * @authenticated
     *
     * @bodyParam name string optional Display name. Example: John Doe
     * @bodyParam email string optional Email address. Example: john@example.com
     * @bodyParam language string optional Language code (en, id, ko, ja, zh). Example: id
     *
     * @response scenario=success {
     *   "user": {"id": 1, "name": "Demo", "email": "demo@spine.test", "language": "id"}
     * }
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $userClass = (string) config('auth.providers.users.model');
        $userModel = new $userClass();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'language' => 'sometimes|in:en,id,ko,ja,zh',
        ]);

        if (isset($validated['name'])) {
            $userModel->where('id', $user->id)->update(['name' => $validated['name']]);
        }
        if (isset($validated['email'])) {
            $userModel->where('id', $user->id)->update(['email' => $validated['email']]);
        }
        if (isset($validated['language'])) {
            $userModel->where('id', $user->id)->update(['language' => $validated['language']]);
        }

        $user->refresh();

        return response()->json(['user' => $this->formatUser($user)]);
    }

    /**
     * Change the current user's password.
     *
     * Requires the current password, so a stolen token alone cannot lock the
     * owner out. Every *other* Sanctum token for the account is revoked; the
     * token that made the call survives, because the frontend continues with
     * the same session immediately afterwards.
     *
     * Deliberately not gated on the staff.password.change permission. That
     * permission exists for Perfex parity, but self-service credential
     * rotation has to be available to every authenticated account --
     * requiring a grant to change your own password is a lockout waiting to
     * happen.
     *
     * @authenticated
     *
     * @bodyParam current_password string required The account's current password.
     * @bodyParam new_password string required The replacement, min 12 chars with mixed case, a number and a symbol.
     * @bodyParam new_password_confirmation string required Must match new_password.
     *
     * @response scenario=success {
     *   "success": true,
     *   "message": "Password updated.",
     *   "revoked_sessions": 2
     * }
     * @response 422 {"message": "The current password is incorrect.", "errors": {"current_password": ["..."]}}
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if (!Hash::check($validated['current_password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The current password is incorrect.'),
            ]);
        }

        if (Hash::check($validated['new_password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'new_password' => __('The new password must be different from the current one.'),
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
        ])->save();

        $currentTokenId = $user->currentAccessToken()?->getKey();

        // Revoke the other sessions, keep the one that made this call. A
        // password change that leaves old tokens alive is not a rotation.
        $query = $user->tokens();
        if ($currentTokenId !== null) {
            $query->whereKeyNot($currentTokenId);
        }
        $revoked = $query->delete();

        \Spine\Events\PasswordChanged::dispatch(
            $user,
            $currentTokenId === null ? null : (string) $currentTokenId,
            $revoked
        );

        return response()->json([
            'success' => true,
            'message' => __('Password updated.'),
            'revoked_sessions' => $revoked,
        ]);
    }

    /**
     * Get 2FA status for the current user.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "data": {"enabled": false, "methods": [], "has_totp": false}
     * }
     */
    public function twoFactorStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->ok(app(TwoFactorService::class)->status($user));
    }

    /**
     * Verify 2FA code (TOTP or email) after login challenge.
     *
     * @authenticated
     *
     * @bodyParam code string required 6-digit code. Example: 123456
     * @bodyParam method string TOTP or email. Example: totp
     * @bodyParam challenge_id string required UUID from login response. Example: uuid
     *
     * @response scenario=success {
     *   "token": "1|abc...",
     *   "user": {"id": 1, ...}
     * }
     * @response 422 {"message": "..."}
     */
    public function twoFactorVerify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'method' => ['required', 'in:totp,email'],
            'challenge_id' => ['required', 'uuid'],
        ]);

        $challenge = Cache::get("2fa_challenge:{$validated['challenge_id']}");

        if (! $challenge) {
            return $this->ok(['message' => 'Challenge expired or invalid.'], 401);
        }

        $userClass = (string) config('auth.providers.users.model');
        $user = $userClass::find($challenge['user_id']);

        if (! $user) {
            Cache::forget("2fa_challenge:{$validated['challenge_id']}");

            return $this->ok(['message' => 'User not found.'], 401);
        }

        $twoFactor = app(TwoFactorService::class);

        match ($validated['method']) {
            'totp' => $this->verifyTotp($user, $validated['code']),
            'email' => $this->verifyEmailCode($user, $validated['code'], $validated['challenge_id']),
        };

        Cache::forget("2fa_challenge:{$validated['challenge_id']}");

        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Enable 2FA (TOTP) for the current user.
     *
     * Requires password confirmation. Returns secret + QR code.
     *
     * @authenticated
     *
     * @bodyParam password string required Current password. Example: password
     *
     * @response scenario=success {
     *   "data": {
     *     "secret": "JBSWY3DPEHPK3PXP",
     *     "provisioning_uri": "otpauth://totp/...",
     *     "qr_svg": "<svg>..."
     *   }
     * }
     */
    public function twoFactorEnable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['password'], $user->password)) {
            return $this->ok(['message' => 'Invalid password.'], 422);
        }

        $twoFactor = app(TwoFactorService::class);
        $secret = $twoFactor->generateSecret();
        $issuer = config('app.name', 'Spine');
        $account = $user->email;
        $uri = $twoFactor->provisioningUri($secret, $issuer, $account);
        $qrSvg = $twoFactor->qrCode($secret, $issuer, $account);

        $user->setMeta('2fa_totp_secret', encrypt($secret));
        $user->setMeta('2fa_enabled', true);

        return $this->created([
            'secret' => $secret,
            'provisioning_uri' => $uri,
            'qr_svg' => $qrSvg,
        ]);
    }

    /**
     * Disable 2FA for the current user.
     *
     * Requires password confirmation.
     *
     * @authenticated
     *
     * @bodyParam password string required Current password. Example: password
     *
     * @response scenario=success {"data": {"message": "2FA disabled"}}
     */
    public function twoFactorDisable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['password'], $user->password)) {
            return $this->ok(['message' => 'Invalid password.'], 422);
        }

        $user->deleteMeta('2fa_totp_secret');
        $user->deleteMeta('2fa_enabled');

        return $this->ok(['message' => '2FA disabled.']);
    }

    /**
     * Send a 2FA email verification code.
     *
     * @authenticated
     *
     * @bodyParam challenge_id string required UUID from login response. Example: uuid
     *
     * @response scenario=success {
     *   "data": {
     *     "verification_id": "uuid",
     *     "expires_at": "2026-09-24T...",
     *     "attempts_left": 5
     *   }
     * }
     */
    public function twoFactorEmailSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'challenge_id' => ['required', 'uuid'],
        ]);

        $challenge = Cache::get("2fa_challenge:{$validated['challenge_id']}");

        if (! $challenge) {
            return $this->ok(['message' => 'Challenge expired or invalid.'], 401);
        }

        $userClass = (string) config('auth.providers.users.model');
        $user = $userClass::find($challenge['user_id']);

        if (! $user) {
            Cache::forget("2fa_challenge:{$validated['challenge_id']}");

            return $this->ok(['message' => 'User not found.'], 401);
        }

        $result = app(TwoFactorService::class)->sendEmailCode(
            $user->id,
            $user->email,
            $user->language ?? 'en'
        );

        return $this->ok($result);
    }

    /**
     * Format user payload with permissions and roles.
     *
     * `permissions` must be the *effective* set, i.e. direct grants plus
     * everything inherited through roles. Reading the direct relation instead
     * reports an empty list for any role-based account, so is_admin says true
     * while every permission-gated feature sees none.
     */
    private function formatUser($user): array
    {
        $attrs = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'ulid' => $user->ulid,
            'language' => $user->language ?? config('app.locale', 'en'),
            'is_admin' => $user->hasRole(config('spine.auth.super_admin_role', 'admin')),
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
            'roles' => $user->roles->pluck('name')->values()->all(),
        ];

        return $attrs;
    }

    /**
     * @return never
     */
    private function verifyTotp($user, string $code): void
    {
        $encrypted = $user->getMeta('2fa_totp_secret');

        if (! $encrypted) {
            throw new \RuntimeException('No TOTP secret found for user.');
        }

        $secret = decrypt($encrypted);

        if (! app(TwoFactorService::class)->verifyTotp($secret, $code)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid 2FA code.'],
            ]);
        }
    }

    /**
     * @return never
     */
    private function verifyEmailCode($user, string $code, string $challengeId): void
    {
        $result = app(TwoFactorService::class)->verifyEmailCode(
            $user->id,
            $code,
            $challengeId
        );

        if (! $result['success']) {
            throw ValidationException::withMessages([
                'code' => [$result['message'] ?? 'Invalid code.'],
            ]);
        }
    }
}
