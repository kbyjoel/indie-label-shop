<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Shipping\Model\ShippingMethodTranslation as BaseShippingMethodTranslation;
use Sylius\Resource\Model\TranslatableInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_shipping_method_translation')]
#[ORM\UniqueConstraint(name: 'sylius_shipping_method_translation_uniq_trans', columns: ['translatable_id', 'locale'])]
class ShippingMethodTranslation extends BaseShippingMethodTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $name;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    #[ORM\ManyToOne(targetEntity: ShippingMethod::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?TranslatableInterface $translatable = null;

    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $locale = null;
}
