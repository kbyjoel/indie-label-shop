<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOption as BaseProductOption;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;
use Sylius\Resource\Model\TranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_option')]
class ProductOption extends BaseProductOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', unique: true)]
    protected $code;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $position;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected $updatedAt;

    protected function createTranslation(): ProductOptionTranslationInterface
    {
        return new ProductOptionTranslation();
    }
}
