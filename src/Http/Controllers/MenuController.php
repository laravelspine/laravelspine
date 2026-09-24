<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MenuController extends \Spine\Http\Controllers\Controller
{
    public function sidebar(): JsonResponse
    {
        $user = Auth::user();
        $menuData = config('menus.sidebar', []);

        // Collect module menu items from active modules
        $moduleItems = $this->collectModuleMenuItems();

        // Merge module items into a dedicated "Modules" group
        if (! empty($moduleItems)) {
            $menuData[] = [
                'group' => 'Modules',
                'items' => $moduleItems,
            ];
        }

        $filtered = [];

        foreach ($menuData as $group) {
            $filteredGroup = [
                'group' => $group['group'],
                'items' => $this->filterItems($group['items'], $user),
            ];
            if (! empty($filteredGroup['items'])) {
                $filtered[] = $filteredGroup;
            }
        }

        return $this->ok($filtered);
    }

    public function quickActions(): JsonResponse
    {
        $user = Auth::user();
        $actions = config('menus.quick_actions', []);
        $filtered = $this->filterItems($actions, $user);

        return $this->ok($filtered);
    }

    public function settingsTabs(): JsonResponse
    {
        $tabs = config('menus.settings_tabs', []);

        return $this->ok($tabs);
    }

    /**
     * Collect menu items from all active modules via manifest.
     *
     * Each module's manifest.php can declare a 'menu' array with items like:
     *   ['slug' => 'sales.invoices', 'label' => 'Invoices', 'icon' => 'file-invoice',
     *    'href' => '/admin/invoices', 'permission' => 'invoices.view', 'position' => 30]
     *
     * Items are converted to sidebar format:
     *   ['id' => slug, 'label' => label, 'icon' => icon, 'path' => href,
     *    'permission' => permission, 'children' => []]
     *
     * @return array<array<string, mixed>>
     */
    private function collectModuleMenuItems(): array
    {
        $items = [];

        foreach (app('modules')->allEnabled() as $module) {
            $manifestFile = $module->getPath() . '/manifest.php';
            if (! is_file($manifestFile)) {
                continue;
            }

            $manifest = require $manifestFile;
            foreach ($manifest['menu'] ?? [] as $item) {
                $label = $item['label'] ?? '';
                if (is_array($label)) {
                    $label = $label['key'] ?? implode('.', array_values($label));
                }
                $items[] = [
                    'id'          => $item['slug'] ?? $item['key'] ?? $label,
                    'label'       => $label,
                    'icon'        => $item['icon'] ?? '🧩',
                    'path'        => $item['href'] ?? $item['route'] ?? '/',
                    'permission'  => $item['permission'] ?? null,
                    'children'    => [],
                    'position'    => $item['position'] ?? 999,
                ];
            }
        }

        // Sort by position (manifest field)
        usort($items, fn ($a, $b) => ($a['position'] ?? 999) <=> ($b['position'] ?? 999));

        return $items;
    }

    /**
     * Recursively filter menu items by permission.
     *
     * @param array<array<string, mixed>> $items
     * @return array<array<string, mixed>>
     */
    private function filterItems(array $items, $user): array
    {
        $result = [];

        foreach ($items as $item) {
            $permission = $item['permission'] ?? null;

            if ($permission !== null && ! $user->can($permission)) {
                continue;
            }

            $filteredItem = $item;
            if (isset($item['children']) && is_array($item['children'])) {
                $filteredItem['children'] = $this->filterItems($item['children'], $user);
            }

            $result[] = $filteredItem;
        }

        return $result;
    }
}
