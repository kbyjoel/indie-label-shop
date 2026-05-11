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

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $pickupPointExternalId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $pickupPointName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $pickupPointAddress = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $pickupPointPostalCode = null;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $pickupPointCity = null;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private ?string $pickupPointCountryCode = null;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $labelUrl = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $labelGeneratedAt = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'shipments')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $order;

    #[ORM\ManyToOne(targetEntity: ShippingMethod::class)]
    #[ORM\JoinColumn(name: 'method_id', referencedColumnName: 'id', nullable: false)]
    protected $method;

    /** @var Collection<array-key, \Sylius\Component\Shipping\Model\ShipmentUnitInterface> */
    #[ORM\OneToMany(mappedBy: 'shipment', targetEntity: OrderItemUnit::class)]
    protected $units;

    public function getPickupPointExternalId(): ?string
    {
        return $this->pickupPointExternalId;
    }

    public function setPickupPointExternalId(?string $pickupPointExternalId): void
    {
        $this->pickupPointExternalId = $pickupPointExternalId;
    }

    public function getPickupPointName(): ?string
    {
        return $this->pickupPointName;
    }

    public function setPickupPointName(?string $pickupPointName): void
    {
        $this->pickupPointName = $pickupPointName;
    }

    public function getPickupPointAddress(): ?string
    {
        return $this->pickupPointAddress;
    }

    public function setPickupPointAddress(?string $pickupPointAddress): void
    {
        $this->pickupPointAddress = $pickupPointAddress;
    }

    public function getPickupPointPostalCode(): ?string
    {
        return $this->pickupPointPostalCode;
    }

    public function setPickupPointPostalCode(?string $pickupPointPostalCode): void
    {
        $this->pickupPointPostalCode = $pickupPointPostalCode;
    }

    public function getPickupPointCity(): ?string
    {
        return $this->pickupPointCity;
    }

    public function setPickupPointCity(?string $pickupPointCity): void
    {
        $this->pickupPointCity = $pickupPointCity;
    }

    public function getPickupPointCountryCode(): ?string
    {
        return $this->pickupPointCountryCode;
    }

    public function setPickupPointCountryCode(?string $pickupPointCountryCode): void
    {
        $this->pickupPointCountryCode = $pickupPointCountryCode;
    }

    public function getLabelUrl(): ?string
    {
        return $this->labelUrl;
    }

    public function setLabelUrl(?string $labelUrl): void
    {
        $this->labelUrl = $labelUrl;
    }

    public function getLabelGeneratedAt(): ?\DateTimeImmutable
    {
        return $this->labelGeneratedAt;
    }

    public function setLabelGeneratedAt(?\DateTimeImmutable $labelGeneratedAt): void
    {
        $this->labelGeneratedAt = $labelGeneratedAt;
    }
}
