<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

#[ORM\Table(name: 'indie_band_translation')]
#[ORM\Index(name: 'indie_band_translation_idx', columns: ['locale', 'object_id', 'field'])]
#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class BandTranslation extends AbstractPersonalTranslation
{
    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    #[ORM\ManyToOne(targetEntity: Band::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;
}
