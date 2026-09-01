<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SMS defaults (reset defaults)
|--------------------------------------------------------------------------
|
| Fallback values when an SMS setting is absent. The DB setting
| (SettingService) wins when present; these apply after a reset.
| Values mirror the SMS tab defaults in src/Config/settings-tabs.php.
*/

return [
    'default' => 'log',

    'drivers' => [
        'log' => [
            'driver' => \Spine\Services\Sms\LogSmsDriver::class,
        ],
        'twilio' => [
            'driver'      => \Spine\Services\Sms\TwilioSmsDriver::class,
            'account_sid' => '',
            'auth_token'  => '',
            'from'        => '',
        ],
    ],
];
