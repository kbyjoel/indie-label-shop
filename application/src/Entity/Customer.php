<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_customer')]
class Customer extends BaseCustomer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $firstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $lastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $emailCanonical;

    #[ORM\OneToOne(mappedBy: 'customer', targetEntity: ShopUser::class, cascade: ['persist'])]
    protected $user;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Order::class)]
    protected $orders;

    #[ORM\ManyToOne(targetEntity: Address::class)]
    #[ORM\JoinColumn(name: 'default_address_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected $defaultAddress;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Address::class, cascade: ['persist'])]
    protected $addresses;

    public function __construct()
    {
        parent::__construct();
        $this->orders = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }
}
