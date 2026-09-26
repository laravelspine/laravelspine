<?php

declare(strict_types=1);

namespace Spine\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spine\Models\User;

/**
 * @extends Factory<User>
 *
 * Deliberately free of fakerphp/faker: this factory ships in the package and is
 * therefore reachable from a production install (`composer install --no-dev`),
 * where faker is absent. Defaults are generated with Str instead.
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => 'User '.Str::upper(Str::random(6)),
            'email' => Str::lower(Str::random(10)).'@example.test',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'language' => 'en',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');
            $role = \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => config('spine.rbac.super_admin_role', 'admin'),
                'guard_name' => $guard,
            ]);
            $user->assignRole($role);
        });
    }
}
