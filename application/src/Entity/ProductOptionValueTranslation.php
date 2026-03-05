<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionValueTranslation as BaseProductOptionValueTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_option_value_translation')]
class ProductOptionValueTranslation extends BaseProductOptionValueTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $value;
}
