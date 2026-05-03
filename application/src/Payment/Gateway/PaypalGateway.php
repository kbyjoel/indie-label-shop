<?php

declare(strict_types=1);

namespace App\Payment\Gateway;

use App\Entity\PaymentMethod;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaypalGateway implements PaymentGatewayInterface
{
    private const SANDBOX_URL = 'https://api-m.sandbox.paypal.com';
    private const LIVE_URL = 'https://api-m.paypal.com';

    public function __construct(
        private readonly PaymentMethod $paymentMethod,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * Pour PayPal, l'initiation retourne uniquement le client_id.
     * La création de commande PayPal se fait à la demande via createOrder().
     *
     * @return array<string, mixed>
     */
    public function initiate(int $amount, string $currency): array
    {
        $credentials = $this->paymentMethod->getCredentials() ?? [];

        return ['paypal_client_id' => $credentials['client_id'] ?? ''];
    }

    /**
     * @param array<string, mixed> $details
     */
    public function verify(array $details): bool
    {
        $orderId = $details['paypal_order_id'] ?? null;
        if (null === $orderId) {
            return false;
        }

        $token = $this->getAccessToken();
        $resp = $this->httpClient->request('GET', $this->baseUrl() . '/v2/checkout/orders/' . $orderId, [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $data = $resp->toArray(false);

        return 'COMPLETED' === ($data['status'] ?? '');
    }

    /**
     * Crée une commande PayPal et retourne son ID.
     *
     * @return array<string, mixed>
     */
    public function createOrder(int $amount, string $currency): array
    {
        $token = $this->getAccessToken();
        $value = number_format($amount / 100, 2, '.', '');

        $resp = $this->httpClient->request('POST', $this->baseUrl() . '/v2/checkout/orders', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => $value,
                    ],
                ]],
            ],
        ]);
        $data = $resp->toArray();

        return ['paypal_order_id' => $data['id']];
    }

    /**
     * Capture une commande PayPal approuvée.
     *
     * @return array<string, mixed>
     */
    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();

        $resp = $this->httpClient->request('POST', $this->baseUrl() . '/v2/checkout/orders/' . $orderId . '/capture', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => new \stdClass(),
        ]);
        $data = $resp->toArray();

        return [
            'paypal_order_id' => $orderId,
            'paypal_capture_status' => $data['status'] ?? '',
        ];
    }

    /**
     * Vérifie la signature d'un webhook PayPal via l'API de vérification.
     *
     * @param array<string, string> $headers
     */
    public function verifyWebhook(array $headers, string $body, string $webhookId): bool
    {
        $token = $this->getAccessToken();

        $resp = $this->httpClient->request('POST', $this->baseUrl() . '/v1/notifications/verify-webhook-signature', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'auth_algo' => $headers['paypal-auth-algo'] ?? '',
                'cert_url' => $headers['paypal-cert-url'] ?? '',
                'transmission_id' => $headers['paypal-transmission-id'] ?? '',
                'transmission_sig' => $headers['paypal-transmission-sig'] ?? '',
                'transmission_time' => $headers['paypal-transmission-time'] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($body, true),
            ],
        ]);
        $data = $resp->toArray(false);

        return 'SUCCESS' === ($data['verification_status'] ?? '');
    }

    public function getClientId(): string
    {
        return (string) (($this->paymentMethod->getCredentials() ?? [])['client_id'] ?? '');
    }

    private function getAccessToken(): string
    {
        $credentials = $this->paymentMethod->getCredentials() ?? [];
        $clientId = $credentials['client_id'] ?? '';
        $secret = $credentials['secret'] ?? '';

        $resp = $this->httpClient->request('POST', $this->baseUrl() . '/v1/oauth2/token', [
            'auth_basic' => [$clientId, $secret],
            'body' => ['grant_type' => 'client_credentials'],
        ]);

        return (string) $resp->toArray()['access_token'];
    }

    private function baseUrl(): string
    {
        $mode = ($this->paymentMethod->getCredentials() ?? [])['mode'] ?? 'sandbox';

        return 'live' === $mode ? self::LIVE_URL : self::SANDBOX_URL;
    }
}
