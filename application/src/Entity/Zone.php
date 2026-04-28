<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\Zone as BaseZone;

#[ORM\Entity(repositoryClass: \App\Repository\ZoneRepository::class)]
#[ORM\Table(name: 'sylius_zone')]
class Zone extends BaseZone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    #[ORM\Column(type: 'string', length: 255)]
    protected $type;

    #[ORM\Column(type: 'string', length: 255)]
    protected $scope = 'all';

    #[ORM\OneToMany(mappedBy: 'belongsTo', targetEntity: ZoneMember::class, cascade: ['all'], orphanRemoval: true)]
    protected $members;

    #[ORM\Column(type: 'integer')]
    protected $priority = 0;
}
