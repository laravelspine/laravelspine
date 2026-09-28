<?php

declare(strict_types=1);

/**
 * CONTOH MANIFEST MODUL — kontrak frontend.
 *
 * Ini jembatan antara modul backend dan frontend (nextjs-spine):
 * - 'menu'    → item yang dirender Sidebar
 * - 'widgets' → widget yang dirender Dashboard per area
 * - 'detail_tabs' → panel detail per record (api placeholder {id})
 *
 * @return array{frontend: array{entry_url: string}, menu: list<array>, widgets: list<array>, detail_tabs: list<array>, settings: list<array>, rbac: array}
 */
return [
    'frontend' => [
        'entry_url' => '/api/v1/modules/assets/region/region.module.js',
    ],

    'menu' => [
        [
            'slug'     => 'region',
            'label'    => ['namespace' => 'module.region', 'key' => 'menu'],
            'icon'     => '🗺️',
            'href'     => '/region',
            'position' => 80,
        ],
    ],

    'widgets' => [
        [
            'id'    => 'region-provinces',
            'area'  => 'right-4',
            'title' => ['namespace' => 'module.region', 'key' => 'widget_provinces'],
            'api'   => '/regions/provinces',
        ],
    ],

    'detail_tabs' => [
        [
            'slug'     => 'overview',
            'label'    => ['namespace' => 'module.region', 'key' => 'tab_overview'],
            'icon'     => '👁️',
            'api'      => '/regions/provinces/{id}',
            'position' => 10,
        ],
        [
            'slug'     => 'regencies',
            'label'    => ['namespace' => 'module.region', 'key' => 'tab_regencies'],
            'icon'     => '🏙️',
            'api'      => '/regions/provinces/{id}/regencies',
            'position' => 20,
        ],
        [
            'slug'     => 'activity',
            'label'    => ['namespace' => 'module.region', 'key' => 'tab_activity'],
            'icon'     => '🕐',
            'api'      => '/regions/provinces/{id}/activity-logs',
            'position' => 30,
        ],
    ],

    'settings' => [
        [
            'slug'     => 'region',
            'label'    => ['namespace' => 'module.region', 'key' => 'title'],
            'icon'     => '🗺️',
            'position' => 55,
            'fields'   => [
                [
                    'key'     => 'region_default_province',
                    'label'   => 'Default Province',
                    'type'    => 'text',
                    'default' => '',
                ],
                [
                    'key'     => 'region_searchable_columns',
                    'label'   => 'Searchable Columns',
                    'type'    => 'text',
                    'default' => 'name,iso_code',
                ],
                [
                    'key'     => 'region_hide_columns',
                    'label'   => 'Hide Columns',
                    'type'    => 'text',
                    'default' => 'iso_code',
                ],
            ],
        ],
    ],

    'rbac' => [
        'permissions' => [
            'region.view',
            'region.create',
            'region.edit',
            'region.delete',
        ],
    ],
];