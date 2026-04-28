<?php

namespace App\Component\Track\MessageHandler;

use App\Component\Track\Message\EncodeTrackMp3Message;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EncodeTrackMp3Handler
{
    public function __construct(
        private TrackRepository $trackRepository,
        private FilesystemOperator $previewsStorage,
        private EntityManagerInterface $entityManager,
        private string $projectDir,
    ) {
    }

    public function __invoke(EncodeTrackMp3Message $message): void
    {
        $track = $this->trackRepository->find($message->getTrackId());

        if (!$track) {
            return;
        }

        $masterFile = $track->getMasterFile();
        if (!$masterFile || !$masterFile->getFile()) {
            return;
        }

        $filename = $masterFile->getFile()->getFilename();
        $masterPath = $this->projectDir . '/public/uploads/files/' . $filename;

        if (!file_exists($masterPath)) {
            return;
        }

        // Initialisation de FFMpeg
        $ffmpeg = FFMpeg::create();
        $audio = $ffmpeg->open($masterPath);

        // 1. Encodage MP3 128kbps CBR
        $tmpPreviewPath = sys_get_temp_dir() . '/' . uniqid('preview_', true) . '.mp3';
        $format = new Mp3();
        $format->setAudioKiloBitrate(128);

        $audio->save($format, $tmpPreviewPath);

        // 2. Extraction de la Waveform (image PNG via php-ffmpeg)
        $tmpWaveformPath = sys_get_temp_dir() . '/' . uniqid('waveform_', true) . '.png';

        try {
            // La doc php-ffmpeg : $waveform = $audio->waveform(640, 120, array('#00FF00'));
            // On génère une waveform de 640x120 pixels avec une couleur personnalisée
            $waveform = $audio->waveform(640, 120, ['#15C39A']);
            $waveform->save($tmpWaveformPath);
        } catch (\Exception $e) {
            // Si la génération de la waveform échoue, on continue sans
        }

        // Upload des fichiers
        $previewFilename = pathinfo((string) $filename, \PATHINFO_FILENAME) . '.mp3';
        $waveformFilename = pathinfo((string) $filename, \PATHINFO_FILENAME) . '.png';

        // MP3
        $streamMp3 = fopen($tmpPreviewPath, 'r+');
        if ($streamMp3) {
            $this->previewsStorage->writeStream($previewFilename, $streamMp3, ['visibility' => 'public']);
            fclose($streamMp3);
        }

        // Waveform
        if (file_exists($tmpWaveformPath)) {
            $streamWaveform = fopen($tmpWaveformPath, 'r+');
            if ($streamWaveform) {
                $this->previewsStorage->writeStream($waveformFilename, $streamWaveform, ['visibility' => 'public']);
                fclose($streamWaveform);
                $track->setWaveformPath($waveformFilename);
            }
        }

        // Nettoyage
        unlink($tmpPreviewPath);
        if (file_exists($tmpWaveformPath)) {
            unlink($tmpWaveformPath);
        }

        // Mise à jour de l'entité Track
        $track->setPreviewPath($previewFilename);
        $this->entityManager->flush();
    }
}
