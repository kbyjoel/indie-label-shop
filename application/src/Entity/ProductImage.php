<?php

declare(strict_types=1);

namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'indie_product_image')]
class ProductImage extends AttachedImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: 'image')]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
