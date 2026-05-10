<?php

namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use App\Repository\BandPhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: BandPhotoRepository::class)]
#[ORM\Table(name: 'indie_band_photo')]
class BandPhoto extends AttachedImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Band::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(name: 'band_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
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
