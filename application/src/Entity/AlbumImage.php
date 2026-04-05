<?php
/**
 * Created by PhpStorm.
 * User: Aropixel
 * Date: 08/03/2026
 * Time: 21:30
 */

namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\CroppableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'indie_album_image')]
class AlbumImage extends AttachedImage implements CroppableInterface
{
    use CroppableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Album::class, inversedBy: 'artwork')]
    private ?Album $album = null;

    /** @var Collection<int, AlbumImageCrop>|null */
    #[ORM\OneToMany(targetEntity: AlbumImageCrop::class, mappedBy: "artwork", cascade: ["remove", "persist"])]
    protected ?Collection $crops = null;

    public function __construct()
    {
        $this->crops = new ArrayCollection();
    }

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

    public function addCrop(AlbumImageCrop $crop): self
    {
        if (!$this->getCrops()->contains($crop)) {
            $this->getCrops()->add($crop);
            $crop->setImage($this);
        }

        return $this;
    }

    public function removeCrop(AlbumImageCrop $crop): self
    {
        if ($this->getCrops()->contains($crop)) {
            $this->getCrops()->removeElement($crop);
            // set the owning side to null (unless already changed)
            if ($crop->getImage() === $this) {
                $crop->setImage(null);
            }
        }

        return $this;
    }
}
