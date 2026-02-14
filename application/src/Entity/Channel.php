<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string', unique: true)]
    protected $code;

    #[ORM\Column(type: 'string')]
    protected $name;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $description;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $hostname;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $color;
}
