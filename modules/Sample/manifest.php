<?php

declare(strict_types=1);

/**
 * CONTOH MANIFEST MODUL — kontrak frontend.
 *
 * Ini jembatan antara modul backend dan frontend (nextjs-spine):
 * - 'menu'    → item yang dirender Sidebar
 * - 'widgets' → widget yang dirender Dashboard per area
 * - 'settings' → tab settings sistem (admin only)
 * - 'profile_tabs' → tab profile user (semua user bisa akses)
 *
 * @return array{frontend: array{entry_url: string}, menu: list<array>, widgets: list<array>, detail_tabs: list<array>, settings: list<array>, profile_tabs: list<array>}
 */
return [
    'frontend' => [
        'entry_url' => '/api/v1/modules/assets/sample/sample.module.js',
    ],

    'menu' => [
        [
            'slug'     => 'sample',
            'label'    => ['namespace' => 'module.sample', 'key' => 'menu'],
            'icon'     => '📦',
            'href'     => '/sample',
            'position' => 90,
        ],
    ],

    'widgets' => [
        [
            'id'    => 'sample-items',
            'area'  => 'right-4',
            'title' => ['namespace' => 'module.sample', 'key' => 'widget'],
            'api'   => '/api/v1/sample',
        ],
    ],

    'detail_tabs' => [
        [
            'slug'     => 'overview',
            'label'    => ['namespace' => 'module.sample', 'key' => 'tab_overview'],
            'icon'     => '👁️',
            'api'      => '/api/v1/sample/{id}',
            'position' => 10,
        ],
        [
            'slug'     => 'activity',
            'label'    => ['namespace' => 'module.sample', 'key' => 'tab_activity'],
            'icon'     => '🕐',
            'api'      => '/api/v1/sample/{id}/activity-logs',
            'position' => 20,
        ],
    ],

    'settings' => [
        [
            'slug'     => 'sample',
            'label'    => ['namespace' => 'module.sample', 'key' => 'title'],
            'icon'     => '📦',
            'position' => 51,
            'fields'   => [
                [
                    'key'     => 'sample_prefix',
                    'label'   => 'Prefix',
                    'type'    => 'text',
                    'default' => 'SMP',
                ],
                [
                    'key'     => 'sample_max_items',
                    'label'   => 'Max items',
                    'type'    => 'number',
                    'default' => '100',
                ],
                [
                    'key'     => 'sample_notify',
                    'label'   => 'Notify on new item',
                    'type'    => 'checkbox',
                    'default' => '1',
                ],
            ],
        ],
    ],

    'profile_tabs' => [
        [
            'slug'     => 'sample',
            'label'    => ['namespace' => 'module.sample', 'key' => 'profile_tab'],
            'icon'     => '📦',
            'position' => 20,
            'fields'   => [
                [
                    'key'     => 'sample_profile_note',
                    'label'   => 'Profile Note',
                    'type'    => 'textarea',
                    'default' => '',
                ],
            ],
        ],
    ],
];
