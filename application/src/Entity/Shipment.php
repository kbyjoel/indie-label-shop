<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_shipment')]
class Shipment extends BaseShipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $state;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $tracking;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'shipments')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $order;

    #[ORM\ManyToOne(targetEntity: ShippingMethod::class)]
    #[ORM\JoinColumn(name: 'method_id', referencedColumnName: 'id', nullable: false)]
    protected $method;

    /** @var Collection<array-key, \Sylius\Component\Shipping\Model\ShipmentUnitInterface> */
    #[ORM\OneToMany(mappedBy: 'shipment', targetEntity: OrderItemUnit::class)]
    protected $units;
}
