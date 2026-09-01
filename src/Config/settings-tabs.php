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
        'slug'     => 'general',
        'label'    => 'General',
        'icon'     => '⚙️',
        'position' => 5,
        'fields'   => [
            [
                'key'     => 'companyname',
                'label'   => 'Company Name',
                'type'    => 'text',
            ],
            [
                'key'     => 'main_domain',
                'label'   => 'Main Domain',
                'type'    => 'text',
            ],
            [
                'key'     => 'allowed_files',
                'label'   => 'Allowed Upload File Types',
                'type'    => 'text',
                'default' => 'jpg,png,jpeg,gif,pdf,doc,xls,txt,zip',
            ],
            [
                'key'     => 'rtl_support_admin',
                'label'   => 'RTL Support (admin)',
                'type'    => 'checkbox',
                'default' => '0',
            ],
            [
                'key'     => 'rtl_support_client',
                'label'   => 'RTL Support (client)',
                'type'    => 'checkbox',
                'default' => '0',
            ],
        ],
    ],
    [
        'slug'     => 'company',
        'label'    => 'Company Information',
        'icon'     => '🏢',
        'position' => 10,
        'fields'   => [
            [
                'key'     => 'company_name',
                'label'   => 'Company Name',
                'type'    => 'text',
            ],
            [
                'key'     => 'company_address',
                'label'   => 'Address',
                'type'    => 'text',
            ],
            [
                'key'     => 'company_city',
                'label'   => 'City',
                'type'    => 'text',
            ],
            [
                'key'     => 'company_state',
                'label'   => 'State',
                'type'    => 'text',
            ],
            [
                'key'     => 'company_country_code',
                'label'   => 'Country Code',
                'type'    => 'text',
            ],
            [
                'key'     => 'company_postal_code',
                'label'   => 'Postal Code',
                'type'    => 'text',
            ],
            [
                'key'     => 'company_phonenumber',
                'label'   => 'Phone Number',
                'type'    => 'text',
            ],
            [
                'key'     => 'company_vat',
                'label'   => 'VAT Number',
                'type'    => 'text',
            ],
            [
                'key'     => 'company_info_format',
                'label'   => 'Company Info Format',
                'type'    => 'textarea',
                'default' => '{company_name}\n{address}, {city}\n{state}, {zip_code}\n{country_code}\n{phone}\n{vat_number_with_label}',
            ],
        ],
    ],
    [
        'slug'     => 'localization',
        'label'    => 'Localization',
        'icon'     => '🌐',
        'position' => 15,
        'fields'   => [
            [
                'key'     => 'dateformat',
                'label'   => 'Date Format',
                'type'    => 'select',
                'options' => [
                    ['value' => 'Y-m-d', 'label' => 'Y-m-d (2026-09-01)'],
                    ['value' => 'd-m-Y', 'label' => 'd-m-Y (01-09-2026)'],
                    ['value' => 'm/d/Y', 'label' => 'm/d/Y (09/01/2026)'],
                    ['value' => 'd.m.Y', 'label' => 'd.m.Y (01.09.2026)'],
                ],
                'default' => 'Y-m-d',
            ],
            [
                'key'     => 'time_format',
                'label'   => 'Time Format',
                'type'    => 'select',
                'options' => [
                    ['value' => '24', 'label' => '24-hour'],
                    ['value' => '12', 'label' => '12-hour'],
                ],
                'default' => '24',
            ],
            [
                'key'     => 'default_timezone',
                'label'   => 'Default Timezone',
                'type'    => 'select',
                'options' => [
                    ['value' => 'UTC', 'label' => 'UTC'],
                    ['value' => 'Asia/Jakarta', 'label' => 'Asia/Jakarta (WIB)'],
                    ['value' => 'Asia/Makassar', 'label' => 'Asia/Makassar (WITA)'],
                    ['value' => 'Asia/Jayapura', 'label' => 'Asia/Jayapura (WIT)'],
                    ['value' => 'America/New_York', 'label' => 'America/New_York'],
                    ['value' => 'Europe/London', 'label' => 'Europe/London'],
                ],
                'default' => 'UTC',
            ],
            [
                'key'     => 'active_language',
                'label'   => 'Default Language',
                'type'    => 'select',
                'options' => [
                    ['value' => 'english', 'label' => 'English'],
                    ['value' => 'indonesia', 'label' => 'Indonesia'],
                ],
                'default' => 'english',
            ],
        ],
    ],
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
    [
        'slug'     => 'pdf',
        'label'    => 'PDF',
        'icon'     => '📄',
        'position' => 30,
        'fields'   => [
            [
                'key'     => 'pdf_font',
                'label'   => 'PDF Font',
                'type'    => 'select',
                'options' => [
                    ['value' => 'DejaVu Sans', 'label' => 'DejaVu Sans'],
                    ['value' => 'DejaVu Sans Mono', 'label' => 'DejaVu Sans Mono'],
                    ['value' => 'DejaVu Serif', 'label' => 'DejaVu Serif'],
                    ['value' => 'Helvetica', 'label' => 'Helvetica'],
                    ['value' => 'Times', 'label' => 'Times'],
                ],
                'default' => 'DejaVu Sans',
            ],
            [
                'key'     => 'pdf_font_size',
                'label'   => 'Font Size',
                'type'    => 'number',
                'default' => '10',
            ],
            [
                'key'     => 'pdf_logo_width',
                'label'   => 'Logo Width',
                'type'    => 'number',
                'default' => '120',
            ],
            [
                'key'     => 'pdf_table_heading_color',
                'label'   => 'Table Heading Color',
                'type'    => 'text',
                'default' => '#252b39',
            ],
            [
                'key'     => 'pdf_table_heading_text_color',
                'label'   => 'Table Heading Text Color',
                'type'    => 'text',
                'default' => '#ffffff',
            ],
        ],
    ],
    [
        'slug'     => 'misc',
        'label'    => 'Misc',
        'icon'     => '🔧',
        'position' => 40,
        'fields'   => [
            [
                'key'     => 'media_max_file_size_upload',
                'label'   => 'Max Upload File Size (MB)',
                'type'    => 'number',
                'default' => '25',
            ],
            [
                'key'     => 'delete_activity_log_older_then',
                'label'   => 'Delete Activity Log Older Than (months)',
                'type'    => 'number',
                'default' => '2',
            ],
            [
                'key'     => 'tables_pagination_limit',
                'label'   => 'Table Pagination Limit',
                'type'    => 'number',
                'default' => '25',
            ],
            [
                'key'     => 'show_table_export_button',
                'label'   => 'Show Table Export Button',
                'type'    => 'checkbox',
                'default' => '1',
            ],
            [
                'key'     => 'limit_top_search_bar_results_to',
                'label'   => 'Search Results Limit',
                'type'    => 'number',
                'default' => '30',
            ],
        ],
    ],
    [
        'slug'     => 'sms',
        'label'    => 'SMS',
        'icon'     => '📱',
        'position' => 50,
        'fields'   => [
            [
                'key'     => 'sms_driver',
                'label'   => 'SMS Driver',
                'type'    => 'select',
                'options' => [
                    ['value' => 'log', 'label' => 'Log (dev)'],
                    ['value' => 'twilio', 'label' => 'Twilio'],
                ],
                'default' => 'log',
            ],
            [
                'key'     => 'sms_twilio_account_sid',
                'label'   => 'Twilio Account SID',
                'type'    => 'text',
            ],
            [
                'key'     => 'sms_twilio_auth_token',
                'label'   => 'Twilio Auth Token',
                'type'    => 'password',
            ],
            [
                'key'     => 'sms_twilio_from',
                'label'   => 'Twilio From Number',
                'type'    => 'text',
            ],
        ],
    ],
];
