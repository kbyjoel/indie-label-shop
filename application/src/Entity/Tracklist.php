<?php

namespace App\Entity;

use App\Repository\TracklistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TracklistRepository::class)]
#[ORM\Table(name: 'indie_tracklist')]
class Tracklist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'tracklists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    #[ORM\ManyToOne(targetEntity: Track::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Track $track = null;

    #[ORM\Column]
    private int $position = 0;

    /**
     * @var Collection<int, Release>
     */
    #[ORM\ManyToMany(targetEntity: Release::class, inversedBy: 'tracklists')]
    #[ORM\JoinTable(name: 'indie_tracklist_release')]
    private Collection $releases;

    public function __construct()
    {
        $this->releases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        if ($this->track && $album) {
            $this->track->setProduct($album);
        }

        return $this;
    }

    public function getTrack(): ?Track
    {
        return $this->track;
    }

    public function setTrack(?Track $track): static
    {
        $this->track = $track;

        if ($track && $this->album) {
            $track->setProduct($this->album);
        }

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

    /**
     * @return Collection<int, Release>
     */
    public function getReleases(): Collection
    {
        return $this->releases;
    }

    public function addRelease(Release $release): static
    {
        if (!$this->releases->contains($release)) {
            $this->releases->add($release);
        }

        return $this;
    }

    public function removeRelease(Release $release): static
    {
        $this->releases->removeElement($release);

        return $this;
    }
}
