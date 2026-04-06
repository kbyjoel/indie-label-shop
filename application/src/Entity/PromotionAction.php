<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Promotion\Model\PromotionAction as BasePromotionAction;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_promotion_action')]
class PromotionAction extends BasePromotionAction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255)]
    protected $type;

    #[ORM\Column(type: 'json')]
    protected $configuration = [];

    #[ORM\ManyToOne(targetEntity: Promotion::class, inversedBy: 'actions')]
    #[ORM\JoinColumn(name: 'promotion_id', referencedColumnName: 'id', nullable: false)]
    protected $promotion;
}
