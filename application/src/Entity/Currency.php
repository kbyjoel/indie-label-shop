<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Currency\Model\Currency as BaseCurrency;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_currency')]
class Currency extends BaseCurrency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;
}
