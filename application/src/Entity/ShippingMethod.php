<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;
use Sylius\Component\Shipping\Model\ShippingMethodRuleInterface;
use Sylius\Component\Shipping\Model\ShippingMethodTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_shipping_method')]
class ShippingMethod extends BaseShippingMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'boolean')]
    protected $enabled = true;

    #[ORM\ManyToOne(targetEntity: Zone::class)]
    #[ORM\JoinColumn(name: 'zone_id', referencedColumnName: 'id', nullable: true)]
    protected $zone;

    /** @var Collection<array-key, ShippingMethodRuleInterface> */
    #[ORM\OneToMany(mappedBy: 'shippingMethod', targetEntity: ShippingMethodRule::class, cascade: ['all'], orphanRemoval: true)]
    protected $rules;

    /** @var Collection<array-key, ChannelInterface> */
    #[ORM\ManyToMany(targetEntity: Channel::class)]
    #[ORM\JoinTable(name: 'sylius_shipping_method_channels')]
    #[ORM\JoinColumn(name: 'shipping_method_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'channel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $channels;

    #[ORM\Column(type: 'json')]
    protected $configuration = [];

    #[ORM\Column(type: 'string', length: 255)]
    protected $calculator;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $position;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $minDeliveryTimeDays;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $maxDeliveryTimeDays;

    /** @var Collection<string, ShippingMethodTranslation> */
    #[ORM\OneToMany(mappedBy: 'translatable', targetEntity: ShippingMethodTranslation::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true, indexBy: 'locale')]
    protected $translations; // @phpstan-ignore-line property.phpDocType

    public function __construct()
    {
        parent::__construct();
        $this->rules = new ArrayCollection();
        $this->channels = new ArrayCollection();
    }

    public function removeRule(ShippingMethodRuleInterface $rule): void
    {
        // Do NOT call $rule->setShippingMethod(null) here: the FK is nullable: false,
        // and Doctrine would schedule an UPDATE to NULL before the orphanRemoval DELETE,
        // causing a DB constraint violation. orphanRemoval handles the DELETE automatically.
        $this->rules->removeElement($rule);
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    protected function createTranslation(): ShippingMethodTranslationInterface
    {
        return new ShippingMethodTranslation();
    }
}
