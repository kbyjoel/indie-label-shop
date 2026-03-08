<?php

namespace App\Entity;

use App\Entity\Band;
use Aropixel\AdminBundle\Entity\AttachedImage;

use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\CroppableTrait;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'indie_band_image')]
class BandImage extends AttachedImage implements CroppableInterface
{
    use CroppableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Band::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'band_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Band $band = null;


    #[ORM\OneToMany(mappedBy: "image", targetEntity: BandImageCrop::class, cascade: ["remove", "persist"])]
    protected ?Collection $crops = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBand(): ?Band
    {
        return $this->band;
    }

    public function setBand(?Band $band): self
    {
        $this->band = $band;

        return $this;
    }


    public function addCrop(BandImageCrop $crop): self
    {
        if (!$this->crops->contains($crop)) {
            $this->crops[] = $crop;
            $crop->setImage($this);
        }

        return $this;
    }

    public function removeCrop(BandImageCrop $crop): self
    {
        if ($this->crops->contains($crop)) {
            $this->crops->removeElement($crop);
            // set the owning side to null (unless already changed)
            if ($crop->getImage() === $this) {
                $crop->setImage(null);
            }
        }

        return $this;
    }

}
