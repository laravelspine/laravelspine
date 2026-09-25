# Authentication

Spine does not ship auth endpoints. Identity is application-specific. This document covers how to integrate authentication with Spine.

## Overview

| Component | Responsibility |
|-----------|----------------|
| Spine | Provides API infrastructure, expects authenticated requests |
| Consumer | Provides login/register/logout endpoints |
| Sanctum | Issues API tokens / manages sessions |

## Required Setup

### 1. User Model

```php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'sanctum';

    protected $fillable = [
        'name', 'email', 'password', 'language',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
```

### 2. Auth Controller (Consumer)

Create your own auth controller with these endpoints:

```php
// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'language' => $user->language ?? 'en',
                'roles' => $user->roles->pluck('name')->toArray(),
                'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            ],
        ]);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'language' => $user->language ?? 'en',
                'roles' => $user->roles->pluck('name')->toArray(),
                'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            ],
        ]);
    }

    /**
     * PUT /api/v1/auth/me
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'language' => 'sometimes|in:en,id,ko,ja,zh',
        ]);

        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['language'])) $user->language = $validated['language'];
        $user->save();

        return response()->json(['user' => $user]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
```

### 3. Register Routes

```php
// routes/api.php
use App\Http\Controllers\AuthController;

Route::prefix('v1')->group(function () {
    // Public
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/me', [AuthController::class, 'updateProfile']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        
        // Spine routes
        Route::prefix('spine')->group(function () {
            // ... spine routes
        });
    });
});
```

## Multi-Language Support

Store user language preference:

```php
// Migration
$table->string('language', 10)->default('en')->after('email');
```

```php
// Auth controller - include language in response
'user' => [
    // ...
    'language' => $user->language ?? 'en',
]
```

```php
// Frontend - store in localStorage
localStorage.setItem('auth_user', JSON.stringify(user));
localStorage.setItem('user_language', user.language);
```

## Session-Based Auth (Alternative)

For web apps using session auth instead of tokens:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->statelessApi(); // Disable stateless for API
})
```

```php
// Auth controller using session
public function login(Request $request): JsonResponse
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        
        return response()->json([
            'success' => true,
            'user' => Auth::user(),
        ]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
}
```

## 2FA Support (Optional)

For two-factor authentication, extend the login flow:

```php
public function login(Request $request): JsonResponse
{
    $credentials = $request->validate([...]);
    
    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid'], 401);
    }

    $user = Auth::user();
    
    if ($user->two_factor_enabled) {
        // Return challenge, not token
        return response()->json([
            '2fa_required' => true,
            'challenge_id' => Str::random(32),
        ]);
    }

    $token = $user->createToken('api')->plainTextToken;
    return response()->json(['token' => $token, 'user' => $user]);
}
```

## Security Best Practices

1. **Never store tokens in localStorage for sensitive apps** — use httpOnly cookies
2. **Enable CORS properly** — configure `SANCTUM_STATEFUL_DOMAINS`
3. **Rate limit login attempts** — use Laravel's rate limiter
4. **Hash passwords** — use bcrypt/argon2
5. **Validate input** — always validate auth requests

## Related

- [Installation](./installation.md)
- [RBAC](./rbac.md)
- [API Reference](./api.md)
