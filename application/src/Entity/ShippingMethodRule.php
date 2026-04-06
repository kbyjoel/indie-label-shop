<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Shipping\Model\ShippingMethodRule as BaseShippingMethodRule;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_shipping_method_rule')]
class ShippingMethodRule extends BaseShippingMethodRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255)]
    protected $type;

    #[ORM\Column(type: 'json')]
    protected $configuration = [];

    #[ORM\ManyToOne(targetEntity: ShippingMethod::class, inversedBy: 'rules')]
    #[ORM\JoinColumn(name: 'shipping_method_id', referencedColumnName: 'id', nullable: false)]
    protected $shippingMethod;
}
