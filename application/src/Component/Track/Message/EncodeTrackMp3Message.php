<?php

namespace App\Component\Track\Message;

class EncodeTrackMp3Message
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
