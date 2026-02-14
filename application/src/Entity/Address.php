<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address as BaseAddress;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_address')]
class Address extends BaseAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $firstName;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $lastName;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $phoneNumber;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $street;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $company;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $city;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $postcode;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $countryCode;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $provinceCode;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $provinceName;
}
