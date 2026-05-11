<?php

declare(strict_types=1);

namespace App\Component\Shipping\Gateway;

final class LabelResponse
{
    public function __construct(
        public readonly string $trackingNumber,
        public readonly string $labelUrl,
        public readonly string $labelFormat,
    ) {
    }
}
