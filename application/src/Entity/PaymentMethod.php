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
    protected ?string $name = null;

    #[ORM\Column(type: 'boolean')]
    protected $enabled = true;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $gatewayType = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $credentials = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getGatewayType(): ?string
    {
        return $this->gatewayType;
    }

    public function setGatewayType(?string $gatewayType): static
    {
        $this->gatewayType = $gatewayType;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCredentials(): ?array
    {
        return $this->credentials;
    }

    /**
     * @param array<string, mixed>|null $credentials
     */
    public function setCredentials(?array $credentials): static
    {
        $this->credentials = $credentials;

        return $this;
    }
}
