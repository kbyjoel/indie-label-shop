<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PromotionCoupon as BasePromotionCoupon;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_promotion_coupon')]
class PromotionCoupon extends BasePromotionCoupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $code;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $usageLimit;

    #[ORM\Column(type: 'integer')]
    protected $used = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $expiresAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $perCustomerUsageLimit;

    #[ORM\Column(type: 'boolean')]
    protected $reusableFromCancelledOrders = true;

    #[ORM\Column(type: 'datetime')]
    protected $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $updatedAt;

    #[ORM\ManyToOne(targetEntity: Promotion::class, inversedBy: 'coupons')]
    #[ORM\JoinColumn(name: 'promotion_id', referencedColumnName: 'id', nullable: false)]
    protected ?\Sylius\Component\Promotion\Model\PromotionInterface $promotion;
}
