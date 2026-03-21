<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionValue as BaseProductOptionValue;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_option_value')]
class ProductOptionValue extends BaseProductOptionValue
{
    public function __construct()
    {
        parent::__construct();
        $this->setCurrentLocale('fr');
        $this->setFallbackLocale('fr');
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\ManyToOne(targetEntity: ProductOption::class, inversedBy: 'values')]
    #[ORM\JoinColumn(name: 'option_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $option;

    #[ORM\OneToMany(mappedBy: 'translatable', targetEntity: ProductOptionValueTranslation::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true, indexBy: 'locale')]
    protected $translations;

    public function getName(): ?string
    {
        return $this->getTranslation()->getValue();
    }

    public function setName(?string $name): void
    {
        $this->getTranslation()->setValue($name);
    }

    protected function createTranslation(): ProductOptionValueTranslationInterface
    {
        return new ProductOptionValueTranslation();
    }
}
