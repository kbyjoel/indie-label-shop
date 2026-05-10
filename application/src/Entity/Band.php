<?php

namespace App\Entity;

use App\Repository\BandRepository;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use Aropixel\AdminBundle\Entity\TranslatableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;

#[ORM\Entity(repositoryClass: BandRepository::class)]
#[ORM\Table(name: 'indie_band')]
#[Gedmo\TranslationEntity(class: BandTranslation::class)]
class Band implements Translatable
{
    use PublishableTrait;
    use TimestampableEntity;
    use TranslatableTrait;

    /**
     * @var Collection<int, BandTranslation>
     */
    #[ORM\OneToMany(targetEntity: BandTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    protected ?Collection $translations = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $status = 'offline';

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebook = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagram = null;

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Artist>
     */
    #[ORM\ManyToMany(targetEntity: Artist::class)]
    #[ORM\JoinTable(name: 'indie_band_artist')]
    private Collection $members;

    #[ORM\OneToOne(targetEntity: BandImage::class, mappedBy: 'band', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?BandImage $image = null;

    /**
     * @var Collection<int, Concert>
     */
    #[ORM\OneToMany(targetEntity: Concert::class, mappedBy: 'band', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $concerts;

    /**
     * @var Collection<int, BandVideoClip>
     */
    #[ORM\OneToMany(targetEntity: BandVideoClip::class, mappedBy: 'band', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $videoClips;

    /** @phpstan-ignore property.onlyWritten */
    private ?string $translatableLocale = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->concerts = new ArrayCollection();
        $this->videoClips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): static
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): static
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): static
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->translatableLocale = $locale;
    }

    /**
     * @return Collection<int, Artist>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Artist $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(Artist $member): static
    {
        $this->members->removeElement($member);

        return $this;
    }

    public function getImage(): ?BandImage
    {
        return $this->image;
    }

    public function setImage(?BandImage $image): self
    {
        if (null === $image || null === $image->getImage()) {
            $this->image = null;
        } else {
            $this->image = $image;
            $this->image->setBand($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Concert>
     */
    public function getConcerts(): Collection
    {
        return $this->concerts;
    }

    public function addConcert(Concert $concert): static
    {
        if (!$this->concerts->contains($concert)) {
            $this->concerts->add($concert);
            $concert->setBand($this);
        }

        return $this;
    }

    public function removeConcert(Concert $concert): static
    {
        if ($this->concerts->removeElement($concert)) {
            if ($concert->getBand() === $this) {
                $concert->setBand(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BandVideoClip>
     */
    public function getVideoClips(): Collection
    {
        return $this->videoClips;
    }

    public function addVideoClip(BandVideoClip $videoClip): static
    {
        if (!$this->videoClips->contains($videoClip)) {
            $this->videoClips->add($videoClip);
            $videoClip->setBand($this);
        }

        return $this;
    }

    public function removeVideoClip(BandVideoClip $videoClip): static
    {
        if ($this->videoClips->removeElement($videoClip)) {
            if ($videoClip->getBand() === $this) {
                $videoClip->setBand(null);
            }
        }

        return $this;
    }
}
