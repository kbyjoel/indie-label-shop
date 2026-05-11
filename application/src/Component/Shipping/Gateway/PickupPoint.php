<?php

declare(strict_types=1);

namespace App\Component\Shipping\Gateway;

final class PickupPoint
{
    /**
     * @param array<string,string> $openingHours  Ex: ['lundi' => '9h-19h', ...]
     * @param array<string,mixed>  $extra          Données libres par gateway
     */
    public function __construct(
        public readonly string $externalId,
        public readonly string $name,
        public readonly string $address,
        public readonly string $postalCode,
        public readonly string $city,
        public readonly string $countryCode,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?float $distanceKm = null,
        public readonly array $openingHours = [],
        public readonly array $extra = [],
    ) {
    }
}
