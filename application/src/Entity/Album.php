<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
#[ORM\Table(name: 'album')]
class Album implements Translatable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Band::class)]
    private ?Band $band = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $catalogNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $releaseDate = null;

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'offline';

    #[Gedmo\Locale]
    private $locale;

    /**
     * @var Collection<int, Album>
     */
    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: 'album_similar')]
    private Collection $similarAlbums;

    /**
     * @var Collection<int, Artist>
     */
    #[ORM\ManyToMany(targetEntity: Artist::class)]
    #[ORM\JoinTable(name: 'album_artist')]
    private Collection $artists;

    /**
     * @var Collection<int, Release>
     */
    #[ORM\OneToMany(targetEntity: Release::class, mappedBy: 'album', cascade: ['persist', 'remove'])]
    private Collection $releases;

    /**
     * @var Collection<int, Tracklist>
     */
    #[ORM\OneToMany(targetEntity: Tracklist::class, mappedBy: 'album', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $tracklists;

    public function __construct()
    {
        $this->similarAlbums = new ArrayCollection();
        $this->artists = new ArrayCollection();
        $this->releases = new ArrayCollection();
        $this->tracklists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCatalogNumber(): ?string
    {
        return $this->catalogNumber;
    }

    public function setCatalogNumber(?string $catalogNumber): static
    {
        $this->catalogNumber = $catalogNumber;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
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

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Collection<int, Album>
     */
    public function getSimilarAlbums(): Collection
    {
        return $this->similarAlbums;
    }

    public function addSimilarAlbum(Album $similarAlbum): static
    {
        if (!$this->similarAlbums->contains($similarAlbum)) {
            $this->similarAlbums->add($similarAlbum);
        }

        return $this;
    }

    public function removeSimilarAlbum(Album $similarAlbum): static
    {
        $this->similarAlbums->removeElement($similarAlbum);

        return $this;
    }

    /**
     * @return Collection<int, Artist>
     */
    public function getArtists(): Collection
    {
        return $this->artists;
    }

    public function addArtist(Artist $artist): static
    {
        if (!$this->artists->contains($artist)) {
            $this->artists->add($artist);
        }

        return $this;
    }

    public function removeArtist(Artist $artist): static
    {
        $this->artists->removeElement($artist);

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
            $release->setAlbum($this);
        }

        return $this;
    }

    public function removeRelease(Release $release): static
    {
        if ($this->releases->removeElement($release)) {
            // set the owning side to null (unless already changed)
            if ($release->getAlbum() === $this) {
                $release->setAlbum(null);
            }
        }

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
            $tracklist->setAlbum($this);
        }

        return $this;
    }

    public function removeTracklist(Tracklist $tracklist): static
    {
        if ($this->tracklists->removeElement($tracklist)) {
            // set the owning side to null (unless already changed)
            if ($tracklist->getAlbum() === $this) {
                $tracklist->setAlbum(null);
            }
        }

        return $this;
    }
}
