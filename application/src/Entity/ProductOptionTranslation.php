<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionTranslation as BaseProductOptionTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_option_translation')]
class ProductOptionTranslation extends BaseProductOptionTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $name;

    #[ORM\ManyToOne(targetEntity: ProductOption::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?\Sylius\Resource\Model\TranslatableInterface $translatable = null;

    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $locale = null;
}
