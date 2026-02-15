<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
#[ORM\Table(name: 'indie_track')]
class Track extends BaseProductVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\ManyToOne(targetEntity: Album::class)]
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

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $isrc = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lyrics = null;

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
}
