<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    /** @var Collection<array-key, ProductTranslationInterface> */
    #[ORM\OneToMany(mappedBy: 'translatable', targetEntity: ProductTranslation::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    protected $translations;

    /** @var Collection<array-key, Channel> */
    #[ORM\ManyToMany(targetEntity: Channel::class)]
    #[ORM\JoinTable(name: 'sylius_product_channels')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $channels;

    /** @var Collection<array-key, ProductVariant> */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductVariant::class, cascade: ['all'], orphanRemoval: true)]
    protected $variants;

    // Ajout d'autres propriétés au besoin...
}
