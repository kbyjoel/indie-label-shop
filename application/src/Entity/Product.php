<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'sylius_product')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['merch' => Product::class, 'album' => Album::class])]
class Product extends BaseProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'boolean')]
    protected $enabled = false;

    #[ORM\OneToMany(mappedBy: 'translatable', targetEntity: ProductTranslation::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true, indexBy: 'locale')]
    protected $translations;

    #[ORM\ManyToMany(targetEntity: Channel::class)]
    #[ORM\JoinTable(name: 'sylius_product_channels')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $channels;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductVariant::class, cascade: ['all'], orphanRemoval: true)]
    protected $variants;

    #[ORM\ManyToMany(targetEntity: ProductOption::class)]
    #[ORM\JoinTable(name: 'sylius_product_options')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'option_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $options;

    #[ORM\ManyToOne(targetEntity: TaxCategory::class)]
    #[ORM\JoinColumn(name: 'tax_category_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?TaxCategoryInterface $taxCategory = null;

    #[ORM\ManyToOne(targetEntity: Band::class)]
    #[ORM\JoinColumn(name: 'band_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Band $band = null;

    #[ORM\OneToOne(targetEntity: ProductImage::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    private ?ProductImage $image = null;

    public function __construct()
    {
        parent::__construct();
        $this->setCurrentLocale('fr');
        $this->setFallbackLocale('fr');
    }

    public function getName(): ?string
    {
        return $this->getTranslation()->getName();
    }

    public function setName(?string $name): void
    {
        $this->getTranslation()->setName($name);
    }

    public function getBand(): ?Band
    {
        return $this->band;
    }

    public function setBand(?Band $band): void
    {
        $this->band = $band;
    }

    public function getTaxCategory(): ?TaxCategoryInterface
    {
        return $this->taxCategory;
    }

    public function setTaxCategory(?TaxCategoryInterface $taxCategory): void
    {
        $this->taxCategory = $taxCategory;
    }

    public function getImage(): ?ProductImage
    {
        return $this->image;
    }

    public function setImage(?ProductImage $image): void
    {
        if (null === $image || null === $image->getImage()) {
            $this->image = null;
        } else {
            $this->image = $image;
            $this->image->setProduct($this);
        }
    }
}
