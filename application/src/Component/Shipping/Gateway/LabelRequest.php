<?php

declare(strict_types=1);

namespace App\Component\Shipping\Gateway;

final class LabelRequest
{
    public function __construct(
        public readonly int $shipmentId,
        public readonly string $pickupPointExternalId,
        public readonly string $senderName,
        public readonly string $senderAddress,
        public readonly string $senderPostalCode,
        public readonly string $senderCity,
        public readonly string $senderCountryCode,
        public readonly string $recipientName,
        public readonly string $recipientAddress,
        public readonly string $recipientPostalCode,
        public readonly string $recipientCity,
        public readonly string $recipientCountryCode,
        public readonly string $recipientEmail,
        public readonly string $recipientPhone,
        public readonly float $weightKg,
        public readonly ?string $reference = null,
    ) {
    }
}
