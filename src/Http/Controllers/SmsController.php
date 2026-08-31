<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Http\Controllers\Concerns\ApiResponse;
use Spine\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SMS sending API.
 *
 * Pluggable provider abstraction + notification channel.
 *
 * Endpoint:
 *   POST /api/sms/send       -> send an SMS via the active/selected driver
 *   GET  /api/sms/drivers    -> list configured drivers
 *
 * @group api/v1
     * @subgroup Sms
 */
class SmsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SmsService $sms
    ) {}

    /**
     * Send an SMS.
     *
     * @authenticated
     *
     * @bodyParam to string required Destination number (international format). Example: +6281234567890
     * @bodyParam body string required Message body. Example: Kode OTP Anda: 123456
     * @bodyParam driver string optional Driver name (twilio, log). Example: log
     *
     * @response {
     *   "data": { "success": true, "message": "SMS terkirim" }
     * }
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'string', 'max:32'],
            'body' => ['required', 'string', 'max:1600'],
            'driver' => ['nullable', 'string', 'max:50'],
        ]);

        $result = $this->sms->send($validated);

        return $this->ok($result);
    }

    /**
     * List of configured SMS drivers.
     *
     * @authenticated
     *
     * @response {
     *   "data": ["log"]
     * }
     */
    public function drivers(): JsonResponse
    {
        return $this->ok($this->sms->availableDrivers());
    }
}
