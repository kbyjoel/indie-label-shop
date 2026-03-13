<?php

namespace App\Component\Track\EntityListener;

use App\Component\Track\Message\EncodeTrackMp3Message;
use App\Entity\TrackMasterFile;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: TrackMasterFile::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: TrackMasterFile::class)]
class TrackMasterFileListener
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    public function postPersist(TrackMasterFile $trackWavFile): void
    {
        $this->dispatchEncodingMessage($trackWavFile);
    }

    public function postUpdate(TrackMasterFile $trackWavFile): void
    {
        $this->dispatchEncodingMessage($trackWavFile);
    }

    private function dispatchEncodingMessage(TrackMasterFile $trackWavFile): void
    {
        $track = $trackWavFile->getTrack();
        if ($track && $track->getId()) {
            $this->bus->dispatch(new EncodeTrackMp3Message($track->getId()));
        }
    }
}
