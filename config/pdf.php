<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| PDF defaults (reset defaults)
|--------------------------------------------------------------------------
|
| Fallback values used when a PDF setting is absent (deleted/reset). The DB
| setting (SettingService) wins when present; these values apply after a
| reset. Values mirror the PDF tab defaults in src/Config/settings-tabs.php.
*/

return [
    'disk' => 'local',

    'defaults' => [
        'paper' => 'a4',
        'orientation' => 'portrait',
    ],

    // Template options — keyed without the 'pdf_' prefix (pdf_font → pdf.font).
    'font' => 'DejaVu Sans',
    'font_size' => '10',
    'logo_width' => '120',
    'table_heading_color' => '#252b39',
    'table_heading_text_color' => '#ffffff',
];
