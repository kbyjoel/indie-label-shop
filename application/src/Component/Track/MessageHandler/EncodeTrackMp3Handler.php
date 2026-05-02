<?php

namespace App\Component\Track\MessageHandler;

use App\Component\Track\Message\EncodeTrackMp3Message;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Audio\Mp3;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EncodeTrackMp3Handler
{
    public function __construct(
        private TrackRepository $trackRepository,
        private FilesystemOperator $previewsStorage,
        private FilesystemOperator $privateStorage,
        private EntityManagerInterface $entityManager,
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
        $storagePath = 'files/' . $filename;

        // Stream master from private.storage to a local temp file for FFmpeg
        $masterStream = $this->privateStorage->readStream($storagePath);
        $ext = pathinfo((string) $filename, \PATHINFO_EXTENSION) ?: 'flac';
        $tmpMasterPath = sys_get_temp_dir() . '/' . uniqid('master_', true) . '.' . $ext;
        $handle = fopen($tmpMasterPath, 'w+');
        if (false === $handle) {
            fclose($masterStream);

            return;
        }
        stream_copy_to_stream($masterStream, $handle);
        fclose($masterStream);
        fclose($handle);

        $ffmpeg = FFMpeg::create();
        $audio = $ffmpeg->open($tmpMasterPath);

        // 1. Encode MP3 128kbps CBR
        $tmpPreviewPath = sys_get_temp_dir() . '/' . uniqid('preview_', true) . '.mp3';
        $format = new Mp3();
        $format->setAudioKiloBitrate(128);
        $audio->save($format, $tmpPreviewPath);

        // 2. Extract duration from encoded MP3
        if (null === $track->getDuration()) {
            try {
                $secs = (float) FFProbe::create()->format($tmpPreviewPath)->get('duration');
                if ($secs > 0) {
                    $track->setDuration(\sprintf('%d:%02d', (int) floor($secs / 60), (int) round(fmod($secs, 60))));
                }
            } catch (\Throwable) {
            }
        }

        // 3. Generate JSON peaks via PCM extraction
        $basename = pathinfo((string) $filename, \PATHINFO_FILENAME);
        $peaks = $this->generatePeaks($tmpPreviewPath);

        // 4. Upload MP3 preview
        $previewFilename = $basename . '.mp3';
        $streamMp3 = fopen($tmpPreviewPath, 'r');
        if ($streamMp3) {
            $this->previewsStorage->writeStream($previewFilename, $streamMp3, ['visibility' => 'public']);
            fclose($streamMp3);
        }

        // 5. Upload JSON peaks
        $waveformFilename = $basename . '.peaks.json';
        if (!empty($peaks['data'])) {
            $this->previewsStorage->write($waveformFilename, (string) json_encode($peaks), ['visibility' => 'public']);
            $track->setWaveformPath($waveformFilename);
        }

        // Cleanup
        @unlink($tmpMasterPath);
        @unlink($tmpPreviewPath);

        $track->setPreviewPath($previewFilename);
        $this->entityManager->flush();
    }

    /**
     * Extracts PCM via FFmpeg and computes 1000 RMS-normalized peaks.
     *
     * @return array{version:int,channels:int,sample_rate:int,samples_per_pixel:int,bits:int,length:int,data:float[]}|array{}
     */
    private function generatePeaks(string $mp3Path): array
    {
        $tmpPcmPath = sys_get_temp_dir() . '/' . uniqid('pcm_', true) . '.raw';

        exec(\sprintf(
            'ffmpeg -y -i %s -f s16le -ac 1 -ar 8000 %s 2>/dev/null',
            escapeshellarg($mp3Path),
            escapeshellarg($tmpPcmPath)
        ), $out, $code);

        if (0 !== $code || !file_exists($tmpPcmPath) || 0 === filesize($tmpPcmPath)) {
            return [];
        }

        $pcm = file_get_contents($tmpPcmPath);
        @unlink($tmpPcmPath);

        if (false === $pcm || \strlen($pcm) < 2) {
            return [];
        }

        $sampleCount = intdiv(\strlen($pcm), 2);
        $windowCount = 1000;
        $samplesPerWindow = max(1, (int) ceil($sampleCount / $windowCount));

        /** @var int[] $samples */
        $samples = array_values(unpack('v*', $pcm) ?: []);
        // Convert unsigned 16-bit to signed
        $samples = array_map(fn (int $s): int => $s > 32767 ? $s - 65536 : $s, $samples);

        $maxRms = 0.0;
        $rmsValues = [];

        for ($i = 0; $i < $windowCount; ++$i) {
            $window = \array_slice($samples, $i * $samplesPerWindow, $samplesPerWindow);
            if ([] === $window) {
                $rmsValues[] = 0.0;

                continue;
            }
            $sumSquares = array_sum(array_map(fn (int $s): float => (float) ($s * $s), $window));
            $rms = sqrt($sumSquares / \count($window));
            $rmsValues[] = $rms;
            if ($rms > $maxRms) {
                $maxRms = $rms;
            }
        }

        $data = $maxRms > 0
            ? array_map(fn (float $v): float => round($v / $maxRms, 4), $rmsValues)
            : array_fill(0, $windowCount, 0.0);

        return [
            'version' => 2,
            'channels' => 1,
            'sample_rate' => 8000,
            'samples_per_pixel' => $samplesPerWindow,
            'bits' => 8,
            'length' => \count($data),
            'data' => $data,
        ];
    }
}
