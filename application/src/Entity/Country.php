<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\Country as BaseCountry;

#[ORM\Entity(repositoryClass: \App\Repository\CountryRepository::class)]
#[ORM\Table(name: 'sylius_country')]
class Country extends BaseCountry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'boolean')]
    protected $enabled = true;

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: Province::class, cascade: ['all'], orphanRemoval: true)]
    protected $provinces;
}
