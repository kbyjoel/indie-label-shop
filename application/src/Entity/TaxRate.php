<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\TaxRate as BaseTaxRate;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_tax_rate')]
class TaxRate extends BaseTaxRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    protected $amount = 0.0;

    #[ORM\Column(type: 'boolean')]
    protected $includedInPrice = false;

    #[ORM\Column(type: 'string', length: 255)]
    protected $calculator;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne(targetEntity: TaxCategory::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    protected $category;

    #[ORM\ManyToOne(targetEntity: Zone::class)]
    #[ORM\JoinColumn(name: 'zone_id', referencedColumnName: 'id', nullable: false)]
    protected $zone;

    public function getId(): ?int
    {
        return $this->id;
    }
}
