<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\OneToMany(mappedBy: 'translatable', targetEntity: ProductOptionTranslation::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true, indexBy: 'locale')]
    protected $translations;

    #[ORM\OneToMany(mappedBy: 'option', targetEntity: ProductOptionValue::class, cascade: ['all'], orphanRemoval: true)]
    protected $values;

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
