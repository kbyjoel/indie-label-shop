<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Taxation\Model\TaxCategory as BaseTaxCategory;

#[ORM\Entity(repositoryClass: \App\Repository\TaxCategoryRepository::class)]
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

    #[ORM\Column(type: 'boolean')]
    protected bool $defaultForAlbum = false;

    #[ORM\Column(type: 'boolean')]
    protected bool $defaultForTrack = false;

    #[ORM\Column(type: 'boolean')]
    protected bool $defaultForMerch = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDefaultForAlbum(): bool
    {
        return $this->defaultForAlbum;
    }

    public function setDefaultForAlbum(bool $defaultForAlbum): void
    {
        $this->defaultForAlbum = $defaultForAlbum;
    }

    public function isDefaultForTrack(): bool
    {
        return $this->defaultForTrack;
    }

    public function setDefaultForTrack(bool $defaultForTrack): void
    {
        $this->defaultForTrack = $defaultForTrack;
    }

    public function isDefaultForMerch(): bool
    {
        return $this->defaultForMerch;
    }

    public function setDefaultForMerch(bool $defaultForMerch): void
    {
        $this->defaultForMerch = $defaultForMerch;
    }
}
