<?php

declare(strict_types=1);

/**
 * Settings tabs CORE Spine — non-module.
 *
 * Kontrak sama dengan manifest modul: tab {slug,label,icon,position,fields[]}
 * dengan tipe field: text | number | checkbox | select | password | action.
 *
 * Field 'action' = tombol yang memanggil endpoint (action.path) dengan
 * body {<action.body_key>: <nilai field action.from_key>} — dipakai mis.
 * tombol "Test SMTP" di tab Email.
 *
 * Tab baru ditambahkan bertahap (Email → General/Company → PDF/Tags/SMS → Realtime/Misc).
 *
 * @return array<int, array<string, mixed>>
 */
return [
    [
        'slug'     => 'email',
        'label'    => 'Email',
        'icon'     => '✉️',
        'position' => 20,
        'fields'   => [
            [
                'key'     => 'mail_host',
                'label'   => 'SMTP Host',
                'type'    => 'text',
                'default' => 'smtp.example.com',
            ],
            [
                'key'     => 'mail_port',
                'label'   => 'SMTP Port',
                'type'    => 'number',
                'default' => '587',
            ],
            [
                'key'     => 'mail_username',
                'label'   => 'Username',
                'type'    => 'text',
            ],
            [
                'key'     => 'mail_password',
                'label'   => 'Password',
                'type'    => 'password',
            ],
            [
                'key'     => 'mail_encryption',
                'label'   => 'Encryption',
                'type'    => 'select',
                'options' => [
                    ['value' => 'tls', 'label' => 'TLS'],
                    ['value' => 'ssl', 'label' => 'SSL'],
                    ['value' => '', 'label' => 'None'],
                ],
                'default' => 'tls',
            ],
            [
                'key'     => 'mail_from_address',
                'label'   => 'From Address',
                'type'    => 'text',
                'default' => 'no-reply@example.com',
            ],
            [
                'key'     => 'mail_from_name',
                'label'   => 'From Name',
                'type'    => 'text',
                'default' => 'Spine',
            ],
            [
                'key'     => 'mail_test_recipient',
                'label'   => 'Test Recipient',
                'type'    => 'text',
            ],
            [
                'key'      => 'mail_test',
                'label'    => 'Test SMTP',
                'type'     => 'action',
                'action'   => [
                    'method'   => 'POST',
                    'path'     => '/api/v1/mail/test',
                    'from_key' => 'mail_test_recipient',
                    'body_key' => 'to',
                ],
            ],
        ],
    ],
];
