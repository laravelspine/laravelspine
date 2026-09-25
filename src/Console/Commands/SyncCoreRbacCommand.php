<?php

declare(strict_types=1);

namespace Spine\Console\Commands;

use Illuminate\Console\Command;
use Spine\Services\RbacService;

/**
 * Seed core RBAC: roles (admin, employee) + permissions (staff.*, roles.*,
 * settings.*, modules.*, activity_log.*, files.*, etc.) via RbacService.
 *
 * Idempotent — safe to re-run.
 */
class SyncCoreRbacCommand extends Command
{
    protected $signature = 'rbac:sync-core {--force : Re-sync even if already seeded}';
    protected $description = 'Seed core roles and permissions (admin, employee, staff.*, etc.)';

    public function handle(RbacService $rbac): int
    {
        $spec = $this->coreSpec();

        $stats = $rbac->sync($spec);

        $this->info("Core RBAC synced:");
        $this->line("  permissions: {$stats['permissions']}");
        $this->line("  roles:       {$stats['roles']}");
        $this->line("  grants:      {$stats['grants']}");
        foreach ($stats['skipped'] as $skip) {
            $this->warn("  skipped: {$skip}");
        }

        return self::SUCCESS;
    }

    /**
     * @return array{permissions: list<string>, roles: list<array{name: string, label?: string, permissions?: list<string>}>, grants?: array<string, list<string>>}
     */
    protected function coreSpec(): array
    {
        return [
            'permissions' => [
                // Staff
                'staff.view', 'staff.view_own', 'staff.create', 'staff.edit', 'staff.delete',
                'staff.profile.edit', 'staff.password.change', 'staff.two_factor.manage',
                // Roles
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                // Settings
                'settings.view', 'settings.edit',
                // Modules
                'modules.view', 'modules.manage',
                // Activity log
                'activity_log.view', 'activity_log.delete',
                // Files
                'files.view', 'files.upload', 'files.delete',
                // Notifications
                'notifications.view', 'notifications.delete',
                // Mail
                'mail.view', 'mail.send', 'mail.queue.manage',
                // Dashboard
                'dashboard.view',
                // System
                'system.view', 'system.settings',
                // GDPR
                'gdpr.export', 'gdpr.anonymize', 'gdpr.delete',
                // Tags
                'tags.view', 'tags.manage',
                // Excel
                'excel.export', 'excel.import',
                // Public content
                'public.content.view',
            ],
            'roles' => [
                [
                    'name' => 'admin',
                    'label' => 'Administrator',
                    'permissions' => ['*'],
                ],
                [
                    'name' => 'employee',
                    'label' => 'Employee',
                    'permissions' => [
                        'staff.view_own', 'staff.profile.edit', 'staff.password.change',
                        'staff.two_factor.manage',
                        'dashboard.view',
                        'notifications.view',
                        'tags.view',
                        'public.content.view',
                    ],
                ],
            ],
            'grants' => [
                'admin' => ['*'],
            ],
        ];
    }
}