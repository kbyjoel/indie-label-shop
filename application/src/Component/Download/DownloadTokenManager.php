<?php

declare(strict_types=1);

namespace App\Component\Download;

use App\Entity\DownloadToken;
use App\Entity\OrderItem;
use App\Repository\DownloadTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DownloadTokenManager
{
    public function __construct(
        private readonly DownloadTokenRepository $downloadTokenRepository,
        private readonly UrlGeneratorInterface $router,
        private readonly string $kernelEnvironment,
        private readonly ?object $s3Client = null,
        private readonly string $cellarBucket = '',
        private readonly string $privateStoragePrefix = 'private',
    ) {
    }

    public function createOrReuse(OrderItem $orderItem, string $format, EntityManagerInterface $em): DownloadToken
    {
        $token = $this->downloadTokenRepository->findForOrderItemAndFormat($orderItem, $format);

        if ($token && $token->isValid()) {
            return $token;
        }

        if ($token) {
            $token->reset();
        } else {
            $token = new DownloadToken();
            $token->setOrderItem($orderItem);
            $token->setFormat($format);
            $em->persist($token);
        }

        $em->flush();

        return $token;
    }

    public function getDownloadUrl(DownloadToken $token): string
    {
        if ('prod' === $this->kernelEnvironment && null !== $this->s3Client && null !== $token->getS3Path()) {
            return $this->generatePresignedUrl($token);
        }

        return $this->router->generate(
            'front_download_file',
            ['tokenValue' => $token->getTokenValue()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function refreshSignedUrl(DownloadToken $token): string
    {
        return $this->getDownloadUrl($token);
    }

    private function generatePresignedUrl(DownloadToken $token): string
    {
        // Requires async-aws/s3 and league/flysystem-async-aws-s3 in production.
        // Install with: composer require async-aws/s3 league/flysystem-async-aws-s3
        $key = $this->privateStoragePrefix . '/' . $token->getS3Path();

        /** @phpstan-ignore-next-line */
        $request = new \AsyncAws\S3\Input\GetObjectRequest([
            'Bucket' => $this->cellarBucket,
            'Key' => $key,
        ]);

        /* @phpstan-ignore-next-line */
        return (string) $this->s3Client->presign($request, new \DateTimeImmutable('+24 hours'));
    }
}
