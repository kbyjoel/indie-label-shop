<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionValueTranslation as BaseProductOptionValueTranslation;
use Sylius\Resource\Model\TranslatableInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_option_value_translation')]
class ProductOptionValueTranslation extends BaseProductOptionValueTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $value;

    #[ORM\ManyToOne(targetEntity: ProductOptionValue::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?TranslatableInterface $translatable = null;

    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $locale = null;
}
