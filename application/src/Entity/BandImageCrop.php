<?php

namespace App\Entity;

use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'indie_band_image_crop')]
class BandImageCrop extends Crop
{
    #[ORM\ManyToOne(targetEntity: BandImage::class, inversedBy: 'crops')]
    protected ?BandImage $image = null;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setImage(?BandImage $image = null): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): BandImage
    {
        \assert(null !== $this->image);

        return $this->image;
    }
}
