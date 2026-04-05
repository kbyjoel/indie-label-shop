<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
#[ORM\Table(name: 'indie_album')]
#[Gedmo\TranslationEntity(class: AlbumTranslation::class)]
class Album extends Product implements Translatable
{
    use PublishableTrait;

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
    private string $status = 'offline';

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, AlbumTranslation>
     */
    #[ORM\OneToMany(targetEntity: AlbumTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    protected ?Collection $albumTranslations = null;

    /** @phpstan-ignore property.onlyWritten */
    private ?string $translatableLocale = null;

    /**
     * @var Collection<int, Album>
     */
    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: 'indie_album_similar')]
    private ?Collection $similarAlbums = null;

    /**
     * @var Collection<int, Artist>
     */
    #[ORM\ManyToMany(targetEntity: Artist::class)]
    #[ORM\JoinTable(name: 'indie_album_artist')]
    private ?Collection $artists = null;

    /**
     * @var Collection<int, Tracklist>
     */
    #[ORM\OneToMany(targetEntity: Tracklist::class, mappedBy: 'album', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private ?Collection $tracklists = null;

    #[ORM\OneToOne(targetEntity: AlbumImage::class, mappedBy: 'album', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?AlbumImage $artwork = null;

    public function __construct()
    {
        parent::__construct();
        $this->similarAlbums = new ArrayCollection();
        $this->artists = new ArrayCollection();
        $this->tracklists = new ArrayCollection();
        $this->albumTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setTranslatableLocale(string $locale): void
    {
        $this->translatableLocale = $locale;
    }

    /**
     * @return Collection<int, AlbumTranslation>
     */
    public function getAlbumTranslations(): Collection
    {
        return $this->albumTranslations ?? new ArrayCollection();
    }

    public function addAlbumTranslation(AlbumTranslation $t): void
    {
        if ($this->albumTranslations && !$this->albumTranslations->contains($t)) {
            $this->albumTranslations[] = $t;
            $t->setObject($this);
        }
    }

    public function removeAlbumTranslation(AlbumTranslation $t): void
    {
        $this->albumTranslations?->removeElement($t);
    }

    /**
     * @return Collection<int, Album>
     */
    public function getSimilarAlbums(): Collection
    {
        return $this->similarAlbums ?? new ArrayCollection();
    }

    public function addSimilarAlbum(Album $similarAlbum): static
    {
        if ($this->similarAlbums && !$this->similarAlbums->contains($similarAlbum)) {
            $this->similarAlbums->add($similarAlbum);
        }

        return $this;
    }

    public function removeSimilarAlbum(Album $similarAlbum): static
    {
        $this->similarAlbums?->removeElement($similarAlbum);

        return $this;
    }

    /**
     * @return Collection<int, Artist>
     */
    public function getArtists(): Collection
    {
        return $this->artists ?? new ArrayCollection();
    }

    public function addArtist(Artist $artist): static
    {
        if ($this->artists && !$this->artists->contains($artist)) {
            $this->artists->add($artist);
        }

        return $this;
    }

    public function removeArtist(Artist $artist): static
    {
        $this->artists?->removeElement($artist);

        return $this;
    }

    /**
     * @return Collection<int, Release>
     */
    public function getReleases(): Collection
    {
        /** @var Collection<int, Release> $releases */
        $releases = $this->getVariants()->filter(fn($variant) => $variant instanceof Release);
        return $releases;
    }

    public function addRelease(Release $release): static
    {
        $this->addVariant($release);
        $release->setAlbum($this);

        return $this;
    }

    public function removeRelease(Release $release): static
    {
        $this->removeVariant($release);
        if ($release->getAlbum() === $this) {
            $release->setAlbum(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Tracklist>
     */
    public function getTracklists(): Collection
    {
        return $this->tracklists ?? new ArrayCollection();
    }

    public function addTracklist(Tracklist $tracklist): static
    {
        if ($this->tracklists && !$this->tracklists->contains($tracklist)) {
            $this->tracklists->add($tracklist);
            $tracklist->setAlbum($this);
        }

        return $this;
    }

    public function removeTracklist(Tracklist $tracklist): static
    {
        if ($this->tracklists?->removeElement($tracklist)) {
            // set the owning side to null (unless already changed)
            if ($tracklist->getAlbum() === $this) {
                $tracklist->setAlbum(null);
            }
        }

        return $this;
    }

    public function getArtwork(): ?AlbumImage
    {
        return $this->artwork;
    }

    public function setArtwork(?AlbumImage $artwork): self
    {
        if ($artwork === null || $artwork->getImage() === null) {
            $this->artwork = null;
        } else {
            $this->artwork = $artwork;
            $this->artwork->setAlbum($this);
        }
        return $this;
    }
}
