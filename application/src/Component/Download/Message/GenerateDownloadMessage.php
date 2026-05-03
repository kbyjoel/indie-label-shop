<?php

declare(strict_types=1);

namespace App\Component\Download\Message;

class GenerateDownloadMessage
{
    public function __construct(
        private readonly int $orderItemId,
        private readonly string $format,
        private readonly int $tokenId,
    ) {
    }

    public function getOrderItemId(): int
    {
        return $this->orderItemId;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getTokenId(): int
    {
        return $this->tokenId;
    }
}
