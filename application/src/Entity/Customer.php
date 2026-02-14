<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_customer')]
class Customer extends BaseCustomer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string', unique: true)]
    protected $email;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $firstName;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $lastName;
}
