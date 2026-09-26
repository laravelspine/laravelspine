<?php

declare(strict_types=1);

namespace Spine\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spine\Models\User;

/**
 * Creates the bootstrap admin account.
 *
 * Credentials come from the environment so that no password is ever committed:
 *
 *   ADMIN_EMAIL     (default: admin@spine.test)
 *   ADMIN_PASSWORD  (optional — a strong one is generated when absent)
 *
 * When ADMIN_PASSWORD is not set the seeder generates a 24-char password and
 * prints it to the console exactly once. It is never written to a file or a log,
 * so record it when the seed runs. Set ADMIN_PASSWORD explicitly for CI or for
 * scripted provisioning.
 *
 * Idempotent: an existing account is left untouched, including its password, so
 * re-seeding cannot lock you out of a working environment.
 */
class AdminUserSeeder extends Seeder
{
    private const GENERATED_PASSWORD_LENGTH = 24;

    public function run(): void
    {
        $email = (string) (config('spine.admin.email') ?: env('ADMIN_EMAIL') ?: 'admin@spine.test');
        $name = (string) (config('spine.admin.name') ?: 'Administrator');

        if (User::query()->where('email', $email)->exists()) {
            $this->ensureAdminRole(User::query()->where('email', $email)->firstOrFail());
            $this->command?->warn("Admin already exists ({$email}); password left unchanged.");

            return;
        }

        $configured = env('ADMIN_PASSWORD');
        $password = is_string($configured) && $configured !== ''
            ? $configured
            : $this->generatePassword();

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->language = 'en';
        $user->email_verified_at = now();
        $user->save();

        $this->ensureAdminRole($user);

        $this->command?->info("Admin created: {$email}");

        if ($password !== $configured) {
            $this->command?->newLine();
            $this->command?->warn('ADMIN_PASSWORD was not set, so a password was generated:');
            $this->command?->line("  {$password}");
            $this->command?->newLine();
            $this->command?->warn('Record it now — it is not stored anywhere and cannot be recovered.');
        }
    }

    /**
     * Grant the super-admin role, creating it when RBAC has not been seeded yet.
     */
    private function ensureAdminRole(User $user): void
    {
        $guard = config('spine.rbac.guard') ?: config('permission.defaults.guard', 'sanctum');

        $role = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => config('spine.rbac.super_admin_role', 'admin'),
            'guard_name' => $guard,
        ]);

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }

    /**
     * 24 chars from a symbol set that keeps the value shell- and URL-safe.
     */
    private function generatePassword(): string
    {
        return Str::password(self::GENERATED_PASSWORD_LENGTH, symbols: true);
    }
}
