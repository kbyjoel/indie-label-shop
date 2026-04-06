<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItemUnit as BaseOrderItemUnit;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order_item_unit')]
class OrderItemUnit extends BaseOrderItemUnit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\ManyToOne(targetEntity: OrderItem::class, inversedBy: 'units')]
    #[ORM\JoinColumn(name: 'order_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected \Sylius\Component\Order\Model\OrderItemInterface $orderItem;

    #[ORM\ManyToOne(targetEntity: Shipment::class, inversedBy: 'units')]
    #[ORM\JoinColumn(name: 'shipment_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected $shipment;

    /** @var Collection<array-key, \Sylius\Component\Order\Model\AdjustmentInterface> */
    #[ORM\OneToMany(targetEntity: Adjustment::class, mappedBy: 'orderItemUnit', cascade: ['all'], orphanRemoval: true)]
    protected $adjustments;

}
