<?php

declare(strict_types=1);

namespace App\Component\Mail\Message;

class SendDownloadReadyMessage
{
    public function __construct(
        private readonly int $downloadTokenId,
    ) {
    }

    public function getDownloadTokenId(): int
    {
        return $this->downloadTokenId;
    }
}
