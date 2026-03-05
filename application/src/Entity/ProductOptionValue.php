<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionValue as BaseProductOptionValue;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_option_value')]
class ProductOptionValue extends BaseProductOptionValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', unique: true)]
    protected $code;

    protected function createTranslation(): ProductOptionValueTranslationInterface
    {
        return new ProductOptionValueTranslation();
    }
}
