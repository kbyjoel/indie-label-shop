<?php

declare(strict_types=1);

namespace App\Component\Mail\Message;

class SendOrderConfirmedMessage
{
    public function __construct(
        private readonly int $orderId,
    ) {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
