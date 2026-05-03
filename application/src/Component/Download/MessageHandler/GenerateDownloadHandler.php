<?php

declare(strict_types=1);

namespace App\Component\Download\MessageHandler;

use App\Component\Download\Message\GenerateDownloadMessage;
use App\Component\Mail\Message\SendDownloadReadyMessage;
use App\Entity\DownloadToken;
use App\Entity\Release;
use App\Entity\Track;
use App\Entity\Tracklist;
use App\Repository\DownloadTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class GenerateDownloadHandler
{
    public function __construct(
        private readonly DownloadTokenRepository $downloadTokenRepository,
        private readonly FilesystemOperator $privateStorage,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(GenerateDownloadMessage $message): void
    {
        $token = $this->downloadTokenRepository->find($message->getTokenId());
        if (!$token) {
            return;
        }

        $orderItem = $token->getOrderItem();
        $variant = $orderItem->getVariant();

        if (!$variant instanceof Release) {
            $token->setStatus(DownloadToken::STATUS_FAILED);
            $this->entityManager->flush();

            return;
        }

        $token->setStatus(DownloadToken::STATUS_PROCESSING);
        $this->entityManager->flush();

        try {
            $tracks = $this->getTracksFromRelease($variant);
            if (empty($tracks)) {
                throw new \RuntimeException('No tracks with master files found for this release');
            }

            $order = $orderItem->getOrder();
            if (!$order) {
                throw new \RuntimeException('OrderItem has no associated Order');
            }
            $album = $variant->getAlbum();
            $albumSlug = $album?->getSlug() ?? 'album';
            $format = $message->getFormat();

            $zipPath = $this->generateZip($tracks, $format, $albumSlug);
            $storagePath = \sprintf('temp-downloads/%d/%s/%s.zip', (int) $order->getId(), $format, $albumSlug);

            $stream = fopen($zipPath, 'r');
            if (false === $stream) {
                @unlink($zipPath);

                throw new \RuntimeException('Cannot open generated ZIP file');
            }

            try {
                $this->privateStorage->writeStream($storagePath, $stream);
            } finally {
                fclose($stream);
                @unlink($zipPath);
            }

            $token->setStatus(DownloadToken::STATUS_READY);
            $token->setS3Path($storagePath);
            $token->setExpiresAt(new \DateTimeImmutable('+24 hours'));
            $this->entityManager->flush();

            $tokenId = $token->getId();
            if (null !== $tokenId) {
                $this->messageBus->dispatch(new SendDownloadReadyMessage($tokenId));
            }
        } catch (\Throwable) {
            $token->setStatus(DownloadToken::STATUS_FAILED);
            $this->entityManager->flush();
        }
    }

    /**
     * @param array<int, Track> $tracks
     */
    private function generateZip(array $tracks, string $format, string $albumSlug): string
    {
        $internalExt = 'wav' === $format ? 'wav' : 'mp3';
        $tmpZipPath = sys_get_temp_dir() . '/' . uniqid('download_', true) . '.zip';

        $zip = new \ZipArchive();
        if (true !== $zip->open($tmpZipPath, \ZipArchive::CREATE)) {
            throw new \RuntimeException('Cannot create ZIP archive at ' . $tmpZipPath);
        }

        $tmpFiles = [];

        try {
            foreach ($tracks as $position => $track) {
                $masterFile = $track->getMasterFile();
                if (!$masterFile || !$masterFile->getFile()) {
                    continue;
                }

                $filename = $masterFile->getFile()->getFilename();
                $storagePath = 'files/' . $filename;
                $ext = pathinfo((string) $filename, \PATHINFO_EXTENSION) ?: 'flac';

                $masterStream = $this->privateStorage->readStream($storagePath);
                $tmpMasterPath = sys_get_temp_dir() . '/' . uniqid('master_', true) . '.' . $ext;
                $handle = fopen($tmpMasterPath, 'w+');
                if (false === $handle) {
                    fclose($masterStream);

                    continue;
                }
                stream_copy_to_stream($masterStream, $handle);
                fclose($masterStream);
                fclose($handle);
                $tmpFiles[] = $tmpMasterPath;

                $tmpOutputPath = sys_get_temp_dir() . '/' . uniqid('encoded_', true) . '.' . $internalExt;
                $tmpFiles[] = $tmpOutputPath;

                $ffmpeg = FFMpeg::create();
                $audio = $ffmpeg->open($tmpMasterPath);

                if ('mp3' === $internalExt) {
                    $fmt = new Mp3();
                    $fmt->setAudioKiloBitrate(320);
                } else {
                    $fmt = new Wav();
                }
                $audio->save($fmt, $tmpOutputPath);

                $trackTitle = $track->getName() ?? ('track-' . ($position + 1));
                $entryName = \sprintf('%02d-%s.%s', $position + 1, $this->sanitizeFilename($trackTitle), $internalExt);
                $zip->addFile($tmpOutputPath, $entryName);
            }

            $zip->close();
        } catch (\Throwable $e) {
            $zip->close();
            foreach ($tmpFiles as $f) {
                @unlink($f);
            }

            throw $e;
        }

        foreach ($tmpFiles as $f) {
            @unlink($f);
        }

        return $tmpZipPath;
    }

    /**
     * @return array<int, Track>
     */
    private function getTracksFromRelease(Release $release): array
    {
        $tracklists = $release->getTracklists()->toArray();

        usort($tracklists, fn (Tracklist $a, Tracklist $b): int => $a->getPosition() <=> $b->getPosition());

        $tracks = [];
        foreach ($tracklists as $tracklist) {
            $track = $tracklist->getTrack();
            if ($track && $track->getMasterFile() && $track->getMasterFile()->getFile()) {
                $tracks[] = $track;
            }
        }

        return $tracks;
    }

    private function sanitizeFilename(string $name): string
    {
        return (string) preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);
    }
}
