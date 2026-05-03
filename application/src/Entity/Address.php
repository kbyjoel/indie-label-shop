<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address as BaseAddress;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_address')]
class Address extends BaseAddress
{
    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'addresses')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected $customer;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $firstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $lastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $phoneNumber;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $street;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $company;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $city;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $postcode;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $countryCode;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $provinceCode;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $provinceName;
}
