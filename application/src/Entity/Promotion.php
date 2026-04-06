<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Promotion as BasePromotion;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_promotion')]
class Promotion extends BasePromotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    #[ORM\Column(type: 'integer')]
    protected $priority = 0;

    #[ORM\Column(type: 'boolean')]
    protected $exclusive = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $usageLimit;

    #[ORM\Column(type: 'integer')]
    protected $used = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $startsAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $endsAt;

    #[ORM\Column(type: 'boolean')]
    protected $couponBased = false;

    #[ORM\Column(type: 'boolean')]
    protected bool $appliesToDiscounted = true;

    #[ORM\Column(type: 'datetime')]
    protected $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $updatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $archivedAt;

    #[ORM\ManyToMany(targetEntity: Channel::class)]
    #[ORM\JoinTable(name: 'sylius_promotion_channels')]
    #[ORM\JoinColumn(name: 'promotion_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'channel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $channels;

    /** @var Collection<array-key, \Sylius\Component\Promotion\Model\PromotionRuleInterface> */
    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: PromotionRule::class, cascade: ['all'], orphanRemoval: true)]
    protected $rules;

    /** @var Collection<array-key, \Sylius\Component\Promotion\Model\PromotionActionInterface> */
    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: PromotionAction::class, cascade: ['all'], orphanRemoval: true)]
    protected $actions;

    /** @var Collection<array-key, \Sylius\Component\Promotion\Model\PromotionCouponInterface> */
    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: PromotionCoupon::class, cascade: ['all'], orphanRemoval: true)]
    protected $coupons;

    public function __construct()
    {
        parent::__construct();
        $this->channels = new ArrayCollection();
        $this->rules = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->coupons = new ArrayCollection();
    }
}
