<?php

namespace App\Entity;

use App\Repository\ReleaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

#[ORM\Entity(repositoryClass: ReleaseRepository::class)]
#[ORM\Table(name: 'indie_release')]
class Release extends BaseProductVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string', unique: true)]
    protected $code;

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $product;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $position;

    #[ORM\Column(type: 'integer')]
    protected $onHold = 0;

    #[ORM\Column(type: 'integer')]
    protected $onHand = 0;

    #[ORM\Column(type: 'boolean')]
    protected $tracked = false;

    #[ORM\Column(type: 'integer')]
    protected $version = 1;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $media = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'offline';

    /**
     * @var Collection<int, Tracklist>
     */
    #[ORM\ManyToMany(targetEntity: Tracklist::class, mappedBy: 'releases')]
    private Collection $tracklists;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

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
}
