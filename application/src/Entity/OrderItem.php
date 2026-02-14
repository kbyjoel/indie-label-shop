<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order_item')]
class OrderItem extends BaseOrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $order;

    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id', nullable: false)]
    protected $variant;

    #[ORM\Column(type: 'integer')]
    protected $quantity = 1;

    #[ORM\Column(type: 'integer')]
    protected $unitPrice = 0;

    #[ORM\Column(type: 'integer')]
    protected $total = 0;

    /** @var Collection<array-key, OrderItemUnit> */
    #[ORM\OneToMany(mappedBy: 'orderItem', targetEntity: OrderItemUnit::class, cascade: ['all'], orphanRemoval: true)]
    protected $units;
}
