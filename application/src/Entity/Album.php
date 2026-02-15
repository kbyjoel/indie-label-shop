<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
#[ORM\Table(name: 'indie_album')]
class Album extends BaseProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    /** @var Collection<array-key, ProductTranslationInterface> */
    #[ORM\OneToMany(mappedBy: 'translatable', targetEntity: ProductTranslation::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    protected $translations;

    #[ORM\ManyToMany(targetEntity: Channel::class)]
    #[ORM\JoinTable(name: 'indie_album_channels')]
    #[ORM\JoinColumn(name: 'album_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'channel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $channels;

    /** @var Collection<array-key, Release> */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Release::class, cascade: ['all'], orphanRemoval: true)]
    protected $variants;

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

    #[ORM\Column(length: 20)]
    private ?string $status = 'offline';

    /**
     * @var Collection<int, Album>
     */
    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: 'indie_album_similar')]
    private Collection $similarAlbums;

    /**
     * @var Collection<int, Artist>
     */
    #[ORM\ManyToMany(targetEntity: Artist::class)]
    #[ORM\JoinTable(name: 'indie_album_artist')]
    private Collection $artists;

    /**
     * @var Collection<int, Release>
     */
    protected Collection $releases;

    /**
     * @var Collection<int, Tracklist>
     */
    #[ORM\OneToMany(targetEntity: Tracklist::class, mappedBy: 'album', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $tracklists;

    public function __construct()
    {
        parent::__construct();
        $this->similarAlbums = new ArrayCollection();
        $this->artists = new ArrayCollection();
        $this->releases = $this->variants;
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

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getName(): ?string
    {
        return $this->title;
    }

    public function setName(?string $name): void
    {
        $this->title = $name;
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

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
