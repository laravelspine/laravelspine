<?php

return [
    'sidebar' => [
        [
            'group' => 'Main',
            'items' => [
                [
                    'id' => 'dashboard',
                    'label' => 'Dashboard',
                    'icon' => '📊',
                    'path' => '/dashboard',
                    'permission' => null,
                ],
                [
                    'id' => 'staff',
                    'label' => 'Staff',
                    'icon' => '👥',
                    'path' => '/staff',
                    'permission' => 'staff.view',
                    'children' => [
                        ['id' => 'staff.list', 'label' => 'All Staff', 'path' => '/staff', 'permission' => 'staff.view'],
                        ['id' => 'staff.create', 'label' => 'Add Staff', 'path' => '/staff/create', 'permission' => 'staff.create'],
                    ],
                ],
                [
                    'id' => 'clients',
                    'label' => 'Clients',
                    'icon' => '🏢',
                    'path' => '/clients',
                    'permission' => null,
                    'children' => [
                        ['id' => 'clients.list', 'label' => 'All Clients', 'path' => '/clients', 'permission' => null],
                        ['id' => 'clients.create', 'label' => 'Add Client', 'path' => '/clients/create', 'permission' => null],
                    ],
                ],
                [
                    'id' => 'invoices',
                    'label' => 'Invoices',
                    'icon' => '📄',
                    'path' => '/invoices',
                    'permission' => null,
                    'children' => [
                        ['id' => 'invoices.list', 'label' => 'All Invoices', 'path' => '/invoices', 'permission' => null],
                        ['id' => 'invoices.create', 'label' => 'Create Invoice', 'path' => '/invoices/create', 'permission' => null],
                        ['id' => 'invoices.drafts', 'label' => 'Drafts', 'path' => '/invoices?status=draft', 'permission' => null],
                        ['id' => 'invoices.paid', 'label' => 'Paid', 'path' => '/invoices?status=paid', 'permission' => null],
                    ],
                ],
                [
                    'id' => 'projects',
                    'label' => 'Projects',
                    'icon' => '📁',
                    'path' => '/projects',
                    'permission' => null,
                ],
                [
                    'id' => 'reports',
                    'label' => 'Reports',
                    'icon' => '📈',
                    'path' => '/reports',
                    'permission' => null,
                ],
            ],
        ],
        [
            'group' => 'Administration',
            'items' => [
                [
                    'id' => 'settings',
                    'label' => 'Settings',
                    'icon' => '⚙️',
                    'path' => '/settings/general',
                    'permission' => 'settings.view',
                ],
                [
                    'id' => 'roles',
                    'label' => 'Roles & Permissions',
                    'icon' => '🔐',
                    'path' => '/roles',
                    'permission' => 'roles.view',
                ],
                [
                    'id' => 'activity-log',
                    'label' => 'Activity Log',
                    'icon' => '📋',
                    'path' => '/activity-logs',
                    'permission' => 'activity_log.view',
                ],
                [
                    'id' => 'modules',
                    'label' => 'Modules',
                    'icon' => '🧩',
                    'path' => '/modules',
                    'permission' => 'modules.view',
                ],
            ],
        ],
    ],

    'quick_actions' => [
        [
            'id' => 'create_invoice',
            'label' => 'New Invoice',
            'icon' => '➕',
            'path' => '/invoices/create',
            'permission' => null,
            'color' => 'blue',
        ],
        [
            'id' => 'add_client',
            'label' => 'Add Client',
            'icon' => '👤',
            'path' => '/clients/create',
            'permission' => null,
            'color' => 'green',
        ],
        [
            'id' => 'add_staff',
            'label' => 'Add Staff',
            'icon' => '📝',
            'path' => '/staff/create',
            'permission' => 'staff.create',
            'color' => 'purple',
        ],
        [
            'id' => 'view_reports',
            'label' => 'Reports',
            'icon' => '📊',
            'path' => '/reports',
            'permission' => null,
            'color' => 'orange',
        ],
    ],

    'settings_tabs' => include __DIR__ . '/../vendor/spine/laravel-spine/src/Config/settings-tabs.php',
];
