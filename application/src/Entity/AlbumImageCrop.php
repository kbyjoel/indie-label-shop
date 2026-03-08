<?php
/**
 * Created by PhpStorm.
 * User: Aropixel
 * Date: 08/03/2026
 * Time: 21:30
 */

namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'indie_album_image_crop')]
class AlbumImageCrop extends Crop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AlbumImage::class, inversedBy: "crops")]
    protected ?AlbumImage $image = null;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setImage(?AlbumImage $image = null) : self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage() : AttachedImageInterface
    {
        return $this->image;
    }
}
