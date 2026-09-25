<?php

declare(strict_types=1);

/**
 * Profile tabs CORE Spine — per-user settings.
 *
 * Setiap user punya profile settings sendiri (name, email, language, dll).
 * Tab ini diakses oleh user yang sedang login (bukan admin saja).
 *
 * Module dapat menambahkan profile_tabs di manifest.php.
 *
 * @return array<int, array<string, mixed>>
 */
return [
    [
        'slug'     => 'account',
        'label'    => 'Account',
        'icon'     => '👤',
        'position' => 5,
        'fields'   => [
            [
                'key'     => 'name',
                'label'   => 'Full Name',
                'type'    => 'text',
            ],
            [
                'key'     => 'email',
                'label'   => 'Email',
                'type'    => 'text',
            ],
            [
                'key'     => 'language',
                'label'   => 'Language',
                'type'    => 'select',
                'options' => [
                    ['value' => 'en', 'label' => 'English'],
                    ['value' => 'id', 'label' => 'Indonesia'],
                    ['value' => 'ko', 'label' => '한국어'],
                    ['value' => 'ja', 'label' => '日本語'],
                    ['value' => 'zh', 'label' => '中文'],
                ],
                'default' => 'en',
            ],
        ],
    ],
    [
        'slug'     => 'security',
        'label'    => 'Security',
        'icon'     => '🔐',
        'position' => 10,
        'fields'   => [
            [
                'key'     => 'current_password',
                'label'   => 'Current Password',
                'type'    => 'password',
            ],
            [
                'key'     => 'new_password',
                'label'   => 'New Password',
                'type'    => 'password',
            ],
            [
                'key'     => 'new_password_confirmation',
                'label'   => 'Confirm New Password',
                'type'    => 'password',
            ],
        ],
    ],
    [
        'slug'     => 'notifications',
        'label'    => 'Notifications',
        'icon'     => '🔔',
        'position' => 15,
        'fields'   => [
            [
                'key'     => 'email_notifications',
                'label'   => 'Email Notifications',
                'type'    => 'checkbox',
                'default' => '1',
            ],
            [
                'key'     => 'browser_notifications',
                'label'   => 'Browser Notifications',
                'type'    => 'checkbox',
                'default' => '0',
            ],
        ],
    ],
];