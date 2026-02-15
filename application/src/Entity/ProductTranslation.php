<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductTranslation as BaseProductTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_translation')]
class ProductTranslation extends BaseProductTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    protected $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $metaKeywords;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $metaDescription;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $shortDescription;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?\Sylius\Resource\Model\TranslatableInterface $translatable = null;

    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $locale = null;
}
