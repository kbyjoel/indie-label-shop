<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['merch' => ProductVariant::class, 'release' => Release::class, 'track' => Track::class])]
class ProductVariant extends BaseProductVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $product;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $position;

    #[ORM\Column(type: 'integer')]
    protected $onHold = 0;

    #[ORM\Column(type: 'integer')]
    protected $onHand = 0;

    #[ORM\Column(type: 'boolean')]
    protected $tracked = false;

    #[ORM\Column(type: 'integer')]
    protected $version = 1;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $price = null;

    #[ORM\ManyToMany(targetEntity: ProductOptionValue::class)]
    #[ORM\JoinTable(name: 'sylius_product_variant_option_value')]
    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'option_value_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $optionValues;

    #[ORM\ManyToOne(targetEntity: TaxCategory::class)]
    #[ORM\JoinColumn(name: 'tax_category_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected $taxCategory;

    public function __construct()
    {
        parent::__construct();
        $this->setCurrentLocale('fr');
        $this->setFallbackLocale('fr');
    }

    public function getName(): ?string
    {
        return $this->product?->getName();
    }

    public function setName(?string $name): void
    {
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->product?->getName();
    }

    public function setTitle(?string $title): void
    {
    }

    public function getOptionValuesLabel(): string
    {
        $labels = [];
        foreach ($this->getOptionValues() as $optionValue) {
            $option = $optionValue->getOption();
            if (null !== $option) {
                $labels[] = \sprintf('%s: %s', $option->getName(), $optionValue->getValue());
            }
        }

        return implode(', ', $labels);
    }

    public function getTaxCategory(): ?TaxCategoryInterface
    {
        return $this->taxCategory;
    }

    public function setTaxCategory(?TaxCategoryInterface $taxCategory): void
    {
        $this->taxCategory = $taxCategory;
    }
}
