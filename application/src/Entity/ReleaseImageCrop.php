<?php

namespace App\Entity;

use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'indie_release_image_crop')]
class ReleaseImageCrop extends Crop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ReleaseImage::class, inversedBy: "crops")]
    protected ?ReleaseImage $image = null;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setImage(?ReleaseImage $image = null) : self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage() : ReleaseImage
    {
        assert($this->image !== null);
        return $this->image;
    }
}
