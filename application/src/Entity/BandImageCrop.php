<?php

namespace App\Entity;

use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'indie_band_image_crop')]
class BandImageCrop extends Crop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BandImage::class, inversedBy: "crops")]
    protected ?BandImage $image = null;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setImage(?BandImage $image = null) : self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage() : BandImage
    {
        assert($this->image !== null);
        return $this->image;
    }
}
