<?php

namespace App\Component\Track\Message;

class NormalizeMasterToFlacMessage
{
    public function __construct(
        private int $trackId,
    ) {
    }

    public function getTrackId(): int
    {
        return $this->trackId;
    }
}
