<?php

namespace App\Entity;

use App\Repository\BandVideoClipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: BandVideoClipRepository::class)]
#[ORM\Table(name: 'indie_band_video_clip')]
class BandVideoClip
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $iframeCode = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Band::class, inversedBy: 'videoClips')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Band $band = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIframeCode(): ?string
    {
        return $this->iframeCode;
    }

    public function setIframeCode(string $iframeCode): static
    {
        $this->iframeCode = $iframeCode;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getBand(): ?Band
    {
        return $this->band;
    }

    public function setBand(?Band $band): static
    {
        $this->band = $band;

        return $this;
    }
}
