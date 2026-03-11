<?php

namespace App\Component\Track\MessageHandler;

use App\Component\Track\Message\EncodeTrackMp3Message;
use App\Repository\TrackRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EncodeTrackMp3Handler
{
    public function __construct(
        private TrackRepository $trackRepository,
    ) {
    }

    public function __invoke(EncodeTrackMp3Message $message): void
    {
        $track = $this->trackRepository->find($message->getTrackId());

        if (!$track) {
            return;
        }

        $wavFile = $track->getWavFile();
        if (!$wavFile || !$wavFile->getFile()) {
            return;
        }

        // TODO: Implémenter l'encodage MP3 ici
        // 1. Récupérer le chemin du fichier WAV
        // 2. Utiliser ffmpeg pour convertir en MP3
        // 3. Enregistrer le fichier MP3 et l'associer à la track (probablement via une nouvelle entité TrackMp3File)
    }
}
