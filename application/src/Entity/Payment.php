<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Payment as BasePayment;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment')]
class Payment extends BasePayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string')]
    protected $state = self::STATE_NEW;

    #[ORM\Column(type: 'integer')]
    protected $amount = 0;

    #[ORM\Column(type: 'string')]
    protected $currencyCode;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $order;

    #[ORM\ManyToOne(targetEntity: PaymentMethod::class)]
    #[ORM\JoinColumn(name: 'method_id', referencedColumnName: 'id', nullable: false)]
    protected $method;
}
