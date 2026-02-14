<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\Zone as BaseZone;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_zone')]
class Zone extends BaseZone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string', unique: true)]
    protected $code;

    #[ORM\Column(type: 'string')]
    protected $name;

    #[ORM\Column(type: 'string')]
    protected $type;

    #[ORM\Column(type: 'string')]
    protected $scope = 'all';

    /** @var Collection<array-key, ZoneMember> */
    #[ORM\OneToMany(mappedBy: 'belongsTo', targetEntity: ZoneMember::class, cascade: ['all'], orphanRemoval: true)]
    protected $members;

    #[ORM\Column(type: 'integer')]
    protected $priority = 0;
}
