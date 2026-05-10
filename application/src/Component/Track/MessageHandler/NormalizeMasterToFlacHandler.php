<?php

namespace App\Component\Track\MessageHandler;

use App\Component\Track\Message\EncodeTrackMp3Message;
use App\Component\Track\Message\NormalizeMasterToFlacMessage;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class NormalizeMasterToFlacHandler
{
    public function __construct(
        private TrackRepository $trackRepository,
        private FilesystemOperator $privateStorage,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(NormalizeMasterToFlacMessage $message): void
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
        $storagePath = 'files/' . $filename;
        $ext = strtolower(pathinfo((string) $filename, \PATHINFO_EXTENSION) ?: 'flac');

        $masterStream = $this->privateStorage->readStream($storagePath);
        $tmpInputPath = sys_get_temp_dir() . '/' . uniqid('master_norm_', true) . '.' . $ext;
        $handle = fopen($tmpInputPath, 'w+');
        if (false === $handle) {
            fclose($masterStream);

            return;
        }
        stream_copy_to_stream($masterStream, $handle);
        fclose($masterStream);
        fclose($handle);

        $tmpFlacPath = sys_get_temp_dir() . '/' . uniqid('master_flac_', true) . '.flac';

        try {
            if ('wav' === $ext) {
                exec(\sprintf(
                    'ffmpeg -y -i %s -c:a flac %s 2>/dev/null',
                    escapeshellarg($tmpInputPath),
                    escapeshellarg($tmpFlacPath)
                ), $out, $code);

                if (0 !== $code || !file_exists($tmpFlacPath) || 0 === filesize($tmpFlacPath)) {
                    return;
                }
            } else {
                // Already FLAC — move to normalized path as-is
                rename($tmpInputPath, $tmpFlacPath);
                $tmpInputPath = null;
            }

            $normalizedPath = 'masters/' . $track->getId() . '.flac';

            $stream = fopen($tmpFlacPath, 'r');
            if (false === $stream) {
                return;
            }
            $this->privateStorage->writeStream($normalizedPath, $stream);
            fclose($stream);

            // Remove original upload from files/ prefix
            try {
                $this->privateStorage->delete($storagePath);
            } catch (\Throwable) {
                // Non-blocking: original may already be gone
            }

            $track->setNormalizedMasterPath($normalizedPath);
            $this->entityManager->flush();

            $trackId = $track->getId();
            if (null !== $trackId) {
                $this->bus->dispatch(new EncodeTrackMp3Message($trackId));
            }
        } finally {
            if (null !== $tmpInputPath) {
                @unlink($tmpInputPath);
            }
            @unlink($tmpFlacPath);
        }
    }
}
