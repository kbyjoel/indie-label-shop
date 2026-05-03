<?php

declare(strict_types=1);

namespace App\Component\Payment\Gateway;

use App\Entity\PaymentMethod;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(private readonly PaymentMethod $paymentMethod)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function initiate(int $amount, string $currency): array
    {
        $credentials = $this->paymentMethod->getCredentials() ?? [];
        Stripe::setApiKey($credentials['secret_key'] ?? '');

        $intent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => strtolower($currency),
        ]);

        return [
            'stripe_payment_intent_id' => $intent->id,
            'stripe_client_secret' => $intent->client_secret,
        ];
    }

    /**
     * @param array<string, mixed> $details
     */
    public function verify(array $details): bool
    {
        $credentials = $this->paymentMethod->getCredentials() ?? [];
        Stripe::setApiKey($credentials['secret_key'] ?? '');

        $intentId = $details['stripe_payment_intent_id'] ?? null;
        if (null === $intentId) {
            return false;
        }

        try {
            $intent = PaymentIntent::retrieve((string) $intentId);

            return 'succeeded' === $intent->status;
        } catch (ApiErrorException) {
            return false;
        }
    }

    public function getPublishableKey(): string
    {
        return (string) (($this->paymentMethod->getCredentials() ?? [])['publishable_key'] ?? '');
    }
}
