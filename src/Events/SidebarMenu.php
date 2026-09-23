<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect sidebar menu items from core + modules.
 *
 * Listener returns array of menu items:
 *   ['key' => 'staff', 'label' => 'Staff', 'route' => '/admin/staff', 'icon' => 'users', 'permission' => 'staff.view', 'section' => 'admin', 'sort' => 10]
 *
 * Return value merged, sorted, and filtered by permission by MenuService.
 */
class SidebarMenu
{
    use Dispatchable;

    public array $items = [];

    public function addItem(array $item): void
    {
        $this->items[] = $item;
    }
}