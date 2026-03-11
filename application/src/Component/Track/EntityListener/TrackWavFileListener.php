<?php

namespace App\Component\Track\EntityListener;

use App\Component\Track\Message\EncodeTrackMp3Message;
use App\Entity\TrackWavFile;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: TrackWavFile::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: TrackWavFile::class)]
class TrackWavFileListener
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    public function postPersist(TrackWavFile $trackWavFile): void
    {
        $this->dispatchEncodingMessage($trackWavFile);
    }

    public function postUpdate(TrackWavFile $trackWavFile): void
    {
        $this->dispatchEncodingMessage($trackWavFile);
    }

    private function dispatchEncodingMessage(TrackWavFile $trackWavFile): void
    {
        $track = $trackWavFile->getTrack();
        if ($track && $track->getId()) {
            $this->bus->dispatch(new EncodeTrackMp3Message($track->getId()));
        }
    }
}
