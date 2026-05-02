<?php

declare(strict_types=1);

namespace App\Component\Track;

use App\Entity\Track;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PreviewUrlResolver
{
    public function __construct(
        #[Autowire(env: 'PREVIEWS_BASE_URL')]
        private string $previewsBaseUrl,
    ) {
    }

    public function getPreviewUrl(Track $track): ?string
    {
        return null !== $track->getPreviewPath() ? $this->buildUrl($track->getPreviewPath()) : null;
    }

    public function getWaveformUrl(Track $track): ?string
    {
        return null !== $track->getWaveformPath() ? $this->buildUrl($track->getWaveformPath()) : null;
    }

    private function buildUrl(string $path): string
    {
        if ('' === $this->previewsBaseUrl) {
            return '/previews/' . $path;
        }

        return rtrim($this->previewsBaseUrl, '/') . '/' . $path;
    }
}
