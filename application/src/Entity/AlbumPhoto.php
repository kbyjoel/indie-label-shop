<?php

namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use App\Repository\AlbumPhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: AlbumPhotoRepository::class)]
#[ORM\Table(name: 'indie_album_photo')]
class AlbumPhoto extends AttachedImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(name: 'album_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Album $album = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;

        return $this;
    }
}
