<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $description;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $hostname;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $color;

    #[ORM\ManyToMany(targetEntity: Locale::class)]
    #[ORM\JoinTable(name: 'sylius_channel_locales')]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'locale_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $locales;

    #[ORM\ManyToOne(targetEntity: Locale::class)]
    #[ORM\JoinColumn(name: 'default_locale_id', referencedColumnName: 'id', nullable: false)]
    protected $defaultLocale;

    #[ORM\ManyToMany(targetEntity: Currency::class)]
    #[ORM\JoinTable(name: 'sylius_channel_currencies')]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'currency_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $currencies;

    /** @var Currency|null */
    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[ORM\JoinColumn(name: 'default_currency_id', referencedColumnName: 'id', nullable: false)]
    protected $baseCurrency;

    public function __construct()
    {
        parent::__construct();
        $this->locales = new ArrayCollection();
        $this->currencies = new ArrayCollection();
    }
}
