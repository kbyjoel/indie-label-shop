<?php

namespace App\Entity;

use App\Entity\ReleaseImage;

use App\Repository\ReleaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ReleaseRepository::class)]
#[ORM\Table(name: 'indie_release')]
class Release extends ProductVariant
{
    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $media = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'offline';

    /**
     * @var Collection<int, Tracklist>
     */
    #[ORM\ManyToMany(targetEntity: Tracklist::class, mappedBy: 'releases')]
    private Collection $tracklists;

    #[ORM\OneToOne(targetEntity: ReleaseImage::class, mappedBy: "release", cascade: ["persist", "remove"], orphanRemoval: true)]
    private ?ReleaseImage $image = null;


    public function __construct()
    {
        parent::__construct();
        $this->tracklists = new ArrayCollection();
    }

    public function getAlbum(): ?Album
    {
        return $this->product;
    }

    public function setAlbum(?Album $album): static
    {
        $this->product = $album;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->title;
    }

    public function setName(?string $name): void
    {
        $this->title = $name;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Tracklist>
     */
    public function getTracklists(): Collection
    {
        return $this->tracklists;
    }

    public function addTracklist(Tracklist $tracklist): static
    {
        if (!$this->tracklists->contains($tracklist)) {
            $this->tracklists->add($tracklist);
            $tracklist->addRelease($this);
        }

        return $this;
    }

    public function removeTracklist(Tracklist $tracklist): static
    {
        if ($this->tracklists->removeElement($tracklist)) {
            $tracklist->removeRelease($this);
        }

        return $this;
    }


    public function getImage(): ?ReleaseImage
    {
        return $this->image;
    }

    public function setImage(?ReleaseImage $image): self
    {
        if ($image === null || $image->getImage() === null) {
            $this->image = null;
        } else {
            $this->image = $image;
            $this->image->setRelease($this);
        }
        return $this;
    }

}
