<?php

declare(strict_types=1);

namespace Spine\Services;

use Spine\Services\PaymentGateway\PaymentGatewayInterface;
use Spine\Services\PaymentGateway\StripePaymentGateway;
use Illuminate\Support\Facades\Config;

/**
 * PaymentService — registry + factory for payment gateways.
 *
 * Selects the active gateway based on config, then delegates to the
 * PaymentGatewayInterface implementation.
 */
class PaymentService
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    private array $gateways = [];

    public function __construct()
    {
        $this->registerGateways();
    }

    /**
     * Register all available gateways.
     */
    private function registerGateways(): void
    {
        $this->gateways['stripe'] = new StripePaymentGateway(
            Config::get('payment.stripe.secret_key'),
            Config::get('payment.stripe.webhook_secret')
        );
    }

    /**
     * Get a gateway by name.
     */
    public function gateway(string $name): ?PaymentGatewayInterface
    {
        return $this->gateways[$name] ?? null;
    }

    /**
     * Get the list of configured gateways.
     */
    public function availableGateways(): array
    {
        return array_values(array_filter($this->gateways, fn (PaymentGatewayInterface $g): bool => $g->isConfigured()));
    }

    /**
     * Create a payment intent via the active gateway.
     *
     * @param  array<string, mixed>  $payload
     */
    public function createPaymentIntent(string $gateway, array $payload): array
    {
        $service = $this->gateway($gateway);

        if (!$service) {
            return ['success' => false, 'error' => 'Gateway not found'];
        }

        return $service->createPaymentIntent($payload);
    }

    /**
     * Handle a webhook via the active gateway.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(string $gateway, array $payload): array
    {
        $service = $this->gateway($gateway);

        if (!$service) {
            return ['success' => false, 'error' => 'Gateway not found'];
        }

        return $service->handleWebhook($payload);
    }
}
