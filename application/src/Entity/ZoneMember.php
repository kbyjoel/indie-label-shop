<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\ZoneMember as BaseZoneMember;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_zone_member')]
#[ORM\UniqueConstraint(name: 'zone_member_code_zone_idx', columns: ['code', 'zone_id'])]
class ZoneMember extends BaseZoneMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string')]
    protected $code;

    #[ORM\ManyToOne(targetEntity: Zone::class, inversedBy: 'members')]
    #[ORM\JoinColumn(name: 'zone_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $belongsTo;
}
