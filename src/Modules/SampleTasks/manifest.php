<?php

declare(strict_types=1);

/**
 * CONTOH MANIFEST MODUL CHILD — kontrak frontend.
 *
 * 'menu'  → Sidebar (padanan add_sidebar_menu_item)
 * 'widgets' → Dashboard per area
 * 'detail_tabs' → panel detail per record (api placeholder {id})
 *
 * Tab profile terekspose via UI Extension registry (register di bundle frontend).
 *
 * @return array{frontend: array{entry_url: string}, menu: list<array>, widgets: list<array>, detail_tabs: list<array>, extend_detail_tabs: array}
 */
return [
    // Bundle frontend modul (REACT/UI) — di-import core via import(url) runtime.
    'frontend' => [
        'entry_url' => '/api/v1/modules/assets/sampletasks/sampletasks.module.js',
    ],

    'menu' => [
        [
            'slug'     => 'sample-tasks',
            'label'    => ['namespace' => 'module.sampletasks', 'key' => 'menu'],
            'icon'     => '✅',
            'href'     => '/sample-tasks',
            'position' => 91,
        ],
    ],

    'widgets' => [
        [
            'id'    => 'sample-tasks',
            'area'  => 'right-4',
            'title' => ['namespace' => 'module.sampletasks', 'key' => 'widget'],
            'api'   => '/api/v1/sample-tasks',
        ],
    ],

    'detail_tabs' => [
        [
            'slug'     => 'overview',
            'label'    => ['namespace' => 'module.sampletasks', 'key' => 'tab_overview'],
            'icon'     => '👁️',
            'api'      => '/api/v1/sample-tasks/{id}',
            'position' => 10,
        ],
        [
            'slug'     => 'activity',
            'label'    => ['namespace' => 'module.sampletasks', 'key' => 'tab_activity'],
            'icon'     => '🕐',
            'api'      => '/api/v1/sample-tasks/{id}/activity-logs',
            'position' => 20,
        ],
    ],

    // HOOK tab lintas modul (padanan add_customer_profile_tab legacy):
    // tambahkan tab "Tasks" ke detail module Sample (target 'sample'),
    // diisi daftar child task milik SampleItem tsb (?sample_item_id={id}).
    'extend_detail_tabs' => [
        'sample' => [
            [
                'slug'     => 'tasks',
                'label'    => ['namespace' => 'module.sampletasks', 'key' => 'tab_tasks'],
                'icon'     => '✅',
                'api'      => '/api/v1/sample-tasks?sample_item_id={id}',
                'position' => 30,
            ],
        ],
    ],
];
