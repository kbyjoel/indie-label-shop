<?php

declare(strict_types=1);

namespace App\Component\Shipping\Gateway;

final class PickupPointSearchRequest
{
    public function __construct(
        public readonly string $postalCode,
        public readonly string $city,
        public readonly string $countryCode,
        public readonly ?string $address = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly int $maxResults = 10,
        public readonly float $maxDistanceKm = 20.0,
    ) {
    }
}
