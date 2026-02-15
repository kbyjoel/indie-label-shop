<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Promotion as BasePromotion;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_promotion')]
class Promotion extends BasePromotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'string', length: 255)]
    protected $name;
}
