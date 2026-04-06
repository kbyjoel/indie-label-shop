<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Adjustment as BaseAdjustment;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_adjustment')]
class Adjustment extends BaseAdjustment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255)]
    protected $type;

    #[ORM\Column(type: 'string', length: 255)]
    protected $label;

    #[ORM\Column(type: 'integer')]
    protected $amount = 0;

    #[ORM\Column(type: 'boolean')]
    protected $neutral = false;

    #[ORM\Column(type: 'boolean')]
    protected $locked = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $originCode;

    #[ORM\Column(type: 'json')]
    protected $details = [];

    #[ORM\Column(type: 'datetime')]
    protected $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $updatedAt;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'adjustments')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected $order;

    #[ORM\ManyToOne(targetEntity: OrderItem::class, inversedBy: 'adjustments')]
    #[ORM\JoinColumn(name: 'order_item_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected $orderItem;

    #[ORM\ManyToOne(targetEntity: OrderItemUnit::class, inversedBy: 'adjustments')]
    #[ORM\JoinColumn(name: 'order_item_unit_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected $orderItemUnit;
}
