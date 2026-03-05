<?php

namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\CroppableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BandImage extends AttachedImage implements CroppableInterface
{
    use CroppableTrait;

    #[ORM\Id()]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'band')]
    private ?Band $band = null;

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


}
