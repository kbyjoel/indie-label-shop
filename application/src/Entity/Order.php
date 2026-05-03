<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    protected $number;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', nullable: true)]
    protected $customer;

    #[ORM\ManyToOne(targetEntity: Channel::class)]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', nullable: false)]
    protected $channel;

    #[ORM\ManyToOne(targetEntity: Address::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'shipping_address_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected $shippingAddress;

    #[ORM\ManyToOne(targetEntity: Address::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'billing_address_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected $billingAddress;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    protected $tokenValue;

    #[ORM\Column(type: 'string', length: 255)]
    protected $state = self::STATE_CART;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    protected $currencyCode;

    #[ORM\Column(type: 'integer')]
    protected $total = 0;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['all'], orphanRemoval: true)]
    protected $items;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Shipment::class, cascade: ['all'])]
    protected $shipments;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Payment::class, cascade: ['all'])]
    protected $payments;

    #[ORM\ManyToOne(targetEntity: PromotionCoupon::class)]
    #[ORM\JoinColumn(name: 'promotion_coupon_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected $promotionCoupon;

    /** @var Collection<array-key, \Sylius\Component\Order\Model\AdjustmentInterface> */
    #[ORM\OneToMany(targetEntity: Adjustment::class, mappedBy: 'order', cascade: ['all'], orphanRemoval: true)]
    protected $adjustments;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $confirmationEmailSentAt = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getConfirmationEmailSentAt(): ?\DateTimeImmutable
    {
        return $this->confirmationEmailSentAt;
    }

    public function setConfirmationEmailSentAt(?\DateTimeImmutable $confirmationEmailSentAt): void
    {
        $this->confirmationEmailSentAt = $confirmationEmailSentAt;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function setNumber(?string $number): void
    {
        $this->number = $number;
    }
}
