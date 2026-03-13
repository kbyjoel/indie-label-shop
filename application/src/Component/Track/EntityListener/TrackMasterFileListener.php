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

    public function postPersist(TrackMasterFile $trackMasterFile): void
    {
        $this->dispatchEncodingMessage($trackMasterFile);
    }

    public function postUpdate(TrackMasterFile $trackMasterFile): void
    {
        $this->dispatchEncodingMessage($trackMasterFile);
    }

    private function dispatchEncodingMessage(TrackMasterFile $trackMasterFile): void
    {
        $track = $trackMasterFile->getTrack();
        if ($track && $track->getId()) {
            $this->bus->dispatch(new EncodeTrackMp3Message($track->getId()));
        }
    }
}
