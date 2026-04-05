<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Taxation\Model\TaxCategory as BaseTaxCategory;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_tax_category')]
class TaxCategory extends BaseTaxCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    public function getId(): ?int
    {
        return $this->id;
    }
}
