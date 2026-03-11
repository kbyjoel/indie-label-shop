<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
#[ORM\Table(name: 'indie_track')]
#[ORM\HasLifecycleCallbacks]
class Track extends ProductVariant
{
    #[ORM\PrePersist]
    public function generateCode(): void
    {
        if (!$this->getCode()) {
            $this->setCode(Uuid::v4()->toBase58());
        }
    }

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $isrc = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lyrics = null;

    #[ORM\OneToOne(mappedBy: 'track', targetEntity: TrackWavFile::class, cascade: ['persist', 'remove'])]
    private ?TrackWavFile $wavFile = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->title;
    }

    public function setName(?string $name): void
    {
        $this->title = $name;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getIsrc(): ?string
    {
        return $this->isrc;
    }

    public function setIsrc(?string $isrc): static
    {
        $this->isrc = $isrc;

        return $this;
    }

    public function getLyrics(): ?string
    {
        return $this->lyrics;
    }

    public function setLyrics(?string $lyrics): static
    {
        $this->lyrics = $lyrics;

        return $this;
    }

    public function getWavFile(): ?TrackWavFile
    {
        return $this->wavFile;
    }

    public function setWavFile(?TrackWavFile $wavFile): self
    {
        // set the owning side of the relation if necessary
        if ($wavFile !== null && $wavFile->getTrack() !== $this) {
            $wavFile->setTrack($this);
        }

        $this->wavFile = $wavFile;

        return $this;
    }
}
