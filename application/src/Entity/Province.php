<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\Province as BaseProvince;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_province')]
#[ORM\UniqueConstraint(name: 'province_code_country_idx', columns: ['code', 'country_id'])]
class Province extends BaseProvince
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string')]
    protected $code;

    #[ORM\Column(type: 'string')]
    protected $name;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $abbreviation;

    #[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'provinces')]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $country;
}
