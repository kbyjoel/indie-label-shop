<?php

namespace App\Form\Admin;

use App\Entity\Country;
use App\Entity\Province;
use App\Entity\Zone;
use App\Entity\ZoneMember;
use Aropixel\AdminBundle\Form\Type\CollectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ZoneType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Pays' => 'country',
                    'Province' => 'province',
                    'Zone' => 'zone',
                ],
                'required' => true,
            ])
            ->add('scope', ChoiceType::class, [
                'label' => 'Portée (Scope)',
                'choices' => [
                    'Toutes' => 'all',
                    'Livraison' => 'shipping',
                    'Taxes' => 'tax',
                ],
                'required' => false,
                'empty_data' => 'all',
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'Priorité',
                'required' => true,
            ])
            ->add('members', CollectionType::class, [
                'entry_type' => ZoneMemberType::class,
                'entry_options' => [
                    'zone_type' => $builder->getData()?->getType() ?: 'country',
                ],
                'columns' => [
                    'Code' => [
                        'field' => 'code',
                        'render' => function($field, $item) {
                            /** @var ZoneMember $zoneMember */
                            $zoneMember = $item->vars['data'];
                            if (!$zoneMember) {
                                return 'Nouveau';
                            }

                            $code = $zoneMember->getCode();
                            if (!$code) {
                                return 'Nouveau';
                            }

                            $parentZone = $zoneMember->getBelongsTo();
                            $type = $parentZone?->getType() ?: 'country';

                            $entityClass = match ($type) {
                                'country' => Country::class,
                                'province' => Province::class,
                                'zone' => Zone::class,
                                default => Country::class,
                            };

                            $entity = $this->entityManager->getRepository($entityClass)->findOneBy(['code' => $code]);
                            return $entity ? $entity->getName() : $code;
                        }
                    ],
                ],
                'label' => 'Membres',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
        ]);
    }
}
