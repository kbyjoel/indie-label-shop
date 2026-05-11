<?php

declare(strict_types=1);

namespace App\Component\Shipping\Gateway;

use App\Entity\Order;

final class NullShippingGateway implements ShippingGatewayInterface
{
    public function getCode(): string
    {
        return 'null';
    }

    public function getName(): string
    {
        return 'Test (Null Gateway)';
    }

    /** @param array<string,mixed> $gatewayConfig */
    public function searchPickupPoints(PickupPointSearchRequest $request, array $gatewayConfig = []): array
    {
        return [
            new PickupPoint(
                externalId: 'null-001',
                name: 'Le Relais du Centre',
                address: '12 rue de la Paix',
                postalCode: $request->postalCode,
                city: $request->city ?: 'Ville Test',
                countryCode: $request->countryCode,
                latitude: 48.8566,
                longitude: 2.3522,
                distanceKm: 0.4,
                openingHours: ['lundi-vendredi' => '9h-19h', 'samedi' => '9h-13h'],
            ),
            new PickupPoint(
                externalId: 'null-002',
                name: 'Tabac Presse des Lilas',
                address: '45 avenue Jean Jaurès',
                postalCode: $request->postalCode,
                city: $request->city ?: 'Ville Test',
                countryCode: $request->countryCode,
                latitude: 48.8600,
                longitude: 2.3580,
                distanceKm: 1.1,
                openingHours: ['lundi-samedi' => '7h-20h'],
            ),
            new PickupPoint(
                externalId: 'null-003',
                name: 'Supérette Chez Mario',
                address: '7 place de la République',
                postalCode: $request->postalCode,
                city: $request->city ?: 'Ville Test',
                countryCode: $request->countryCode,
                latitude: 48.8630,
                longitude: 2.3620,
                distanceKm: 2.3,
                openingHours: ['lundi-dimanche' => '8h-22h'],
            ),
        ];
    }

    /** @param array<string,mixed> $gatewayConfig */
    public function getPickupPoint(string $externalId, array $gatewayConfig = []): PickupPoint
    {
        return match ($externalId) {
            'null-001' => new PickupPoint(
                externalId: 'null-001',
                name: 'Le Relais du Centre',
                address: '12 rue de la Paix',
                postalCode: '75001',
                city: 'Paris',
                countryCode: 'FR',
                latitude: 48.8566,
                longitude: 2.3522,
                distanceKm: 0.4,
                openingHours: ['lundi-vendredi' => '9h-19h', 'samedi' => '9h-13h'],
            ),
            'null-002' => new PickupPoint(
                externalId: 'null-002',
                name: 'Tabac Presse des Lilas',
                address: '45 avenue Jean Jaurès',
                postalCode: '75019',
                city: 'Paris',
                countryCode: 'FR',
                latitude: 48.8600,
                longitude: 2.3580,
                distanceKm: 1.1,
                openingHours: ['lundi-samedi' => '7h-20h'],
            ),
            'null-003' => new PickupPoint(
                externalId: 'null-003',
                name: 'Supérette Chez Mario',
                address: '7 place de la République',
                postalCode: '75011',
                city: 'Paris',
                countryCode: 'FR',
                latitude: 48.8630,
                longitude: 2.3620,
                distanceKm: 2.3,
                openingHours: ['lundi-dimanche' => '8h-22h'],
            ),
            default => throw new \InvalidArgumentException(sprintf('Unknown pickup point ID "%s" for null gateway.', $externalId)),
        };
    }

    /** @param array<string,mixed> $gatewayConfig */
    public function generateLabel(LabelRequest $request, array $gatewayConfig = []): LabelResponse
    {
        return new LabelResponse(
            trackingNumber: 'NULL-' . strtoupper(substr(md5((string) $request->shipmentId), 0, 12)),
            labelUrl: 'data:application/pdf;base64,JVBERi0xLjQ=',
            labelFormat: 'pdf',
        );
    }

    /** @param array<string,mixed> $gatewayConfig */
    public function validateConfiguration(array $gatewayConfig): bool
    {
        return true;
    }

    public function getWidgetControllerName(): string
    {
        return 'pickup-point-null';
    }

    /** @param array<string,mixed> $gatewayConfig */
    public function getWidgetConfig(array $gatewayConfig, Order $cart): array
    {
        $address = $cart->getShippingAddress();

        return [
            'postalCode' => $address?->getPostcode() ?? '',
            'city' => $address?->getCity() ?? '',
            'countryCode' => $address?->getCountryCode() ?? 'FR',
        ];
    }
}
