<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment_method')]
class PaymentMethod extends BasePaymentMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    #[ORM\Column(type: 'boolean')]
    protected $enabled = true;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $gatewayType = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $credentials = null;

    public function getGatewayType(): ?string
    {
        return $this->gatewayType;
    }

    public function setGatewayType(?string $gatewayType): static
    {
        $this->gatewayType = $gatewayType;

        return $this;
    }

    public function getCredentials(): ?array
    {
        return $this->credentials;
    }

    public function setCredentials(?array $credentials): static
    {
        $this->credentials = $credentials;

        return $this;
    }
}
