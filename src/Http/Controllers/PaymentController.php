<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Payment gateway abstraction API.
 *
 * The core only exposes the abstraction; each gateway is enabled via config.
 *
 * @group api/v1
     * @subgroup Payment
 */
class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $payment
    ) {}

    /**
     * List configured gateways.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "data": [
     *     {"name": "stripe", "configured": true}
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $gateways = collect($this->payment->availableGateways())->map(fn ($g) => [
            'name' => $g->getName(),
            'configured' => $g->isConfigured(),
        ]);

        return response()->json(['data' => $gateways]);
    }

    /**
     * Create payment intent via gateway.
     *
     * @authenticated
     *
     * @bodyParam gateway string required Gateway name. Example: stripe
     * @bodyParam amount int required Amount in the smallest unit. Example: 50000
     * @bodyParam currency string required Currency code. Example: IDR
     * @bodyParam metadata array<string, mixed> optional Additional metadata.
     *
     * @response scenario=success {
     *   "success": true,
     *   "data": {"id": "pi_...", "client_secret": "pi_..._secret_..."}
     * }
     */
    public function createIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gateway' => 'required|string',
            'amount' => 'required|integer|min:1',
            'currency' => 'sometimes|string|size:3',
            'metadata' => 'sometimes|array',
        ]);

        $result = $this->payment->createPaymentIntent(
            $validated['gateway'],
            [
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'IDR',
                'metadata' => $validated['metadata'] ?? [],
            ]
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Webhook handler for the payment gateway.
     *
     * @response scenario=success {"success": true}
     */
    public function webhook(Request $request, string $gateway): JsonResponse
    {
        $payload = [
            'body' => $request->getContent(),
            'signature' => $request->header('X-Webhook-Signature') ?? '',
        ];

        $result = $this->payment->handleWebhook($gateway, $payload);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}
