<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Entity\ProductVariant;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Product::class)]
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: ProductVariant::class)]
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: ProductOption::class)]
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: ProductOptionValue::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Product::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: ProductVariant::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: ProductOption::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: ProductOptionValue::class)]
class SyliusCodeGeneratorListener
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /** @param LifecycleEventArgs<\Doctrine\ORM\EntityManagerInterface> $args */
    public function prePersist(object $entity, LifecycleEventArgs $args): void
    {
        $this->generateCode($entity);
    }

    /** @param LifecycleEventArgs<\Doctrine\ORM\EntityManagerInterface> $args */
    public function preUpdate(object $entity, LifecycleEventArgs $args): void
    {
        $this->generateCode($entity);
    }

    private function generateCode(object $entity): void
    {
        if (method_exists($entity, 'getCode') && method_exists($entity, 'setCode')) {
            if (!$entity->getCode()) {
                $code = $this->resolveCode($entity);
                if ($code) {
                    $entity->setCode($code);
                }
            }
        }
    }

    private function resolveCode(object $entity): ?string
    {
        $name = null;

        if ($entity instanceof Product) {
            $name = $entity->getName();
        } elseif ($entity instanceof ProductVariant) {
            $name = $entity->getName();
            if ($entity->getProduct() && !$name) {
                $name = $entity->getProduct()->getName();
            }
            // For variants, we might want to append option values if available
            $optionsLabel = $entity->getOptionValuesLabel();
            if ($optionsLabel) {
                $name .= '-' . $optionsLabel;
            }
        } elseif ($entity instanceof ProductOption) {
            $name = $entity->getName();
        } elseif ($entity instanceof ProductOptionValue) {
            $name = $entity->getName();
            if ($entity->getOption() && $entity->getOption()->getName()) {
                $name = $entity->getOption()->getName() . '-' . $name;
            }
        }

        if ($name) {
            return strtoupper($this->slugger->slug($name)->toString());
        }

        return null;
    }
}
