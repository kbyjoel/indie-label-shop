<?php

namespace App\EventListener;

use Sylius\Resource\Model\TranslatableInterface as SyliusTranslatableInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsDoctrineListener(event: Events::postLoad, priority: 8192)]
#[AsDoctrineListener(event: Events::prePersist, priority: 8192)]
class SyliusTranslatableSubscriber
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $lifecycleEventArgs
     */
    public function postLoad(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->setLocales($lifecycleEventArgs);
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $lifecycleEventArgs
     */
    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->setLocales($lifecycleEventArgs);
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $lifecycleEventArgs
     */
    private function setLocales(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();

        if ($entity instanceof SyliusTranslatableInterface) {
            $fallbackLocale = $this->provideFallbackLocale();
            if ($fallbackLocale) {
                $entity->setFallbackLocale($fallbackLocale);
            }

            if ($currentLocale = $this->provideCurrentLocale()) {
                $entity->setCurrentLocale($currentLocale);
            }
        }
    }

    private function provideCurrentLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest instanceof Request) {
            return null;
        }

        $currentLocale = $currentRequest->getLocale();
        if ('' !== $currentLocale) {
            return $currentLocale;
        }

        return null;
    }

    private function provideFallbackLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null !== $currentRequest) {
            return $currentRequest->getDefaultLocale();
        }

        try {
            $locale = $this->parameterBag->get('kernel.default_locale');
            return is_string($locale) ? $locale : null;
        } catch (ParameterNotFoundException|\InvalidArgumentException) {
            return null;
        }
    }
}
