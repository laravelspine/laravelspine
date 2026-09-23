<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect permission catalog from core + modules.
 *
 * Listener returns array of permission definitions:
 *   ['name' => 'staff.view', 'label' => 'View Staff', 'feature' => 'staff', 'capability' => 'view']
 *
 * Return value merged into Spatie Permission table by RbacService.
 */
class PermissionCatalog
{
    use Dispatchable;

    public array $permissions = [];

    public function addPermission(array $perm): void
    {
        $this->permissions[] = $perm;
    }
}