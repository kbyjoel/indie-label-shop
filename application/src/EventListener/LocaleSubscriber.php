<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * In mono-locale mode (single APP_LOCALES value), forces the locale on every
 * master request so controllers and templates never see an unset locale.
 *
 * In multi-locale mode the locale is already provided by the /{_locale} route
 * prefix added by FrontRouteLoader, so this subscriber is a no-op.
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    /** @param list<string> $locales */
    public function __construct(private readonly array $locales) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => [['onKernelRequest', 20]]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (\count($this->locales) !== 1 || !$event->isMainRequest()) {
            return;
        }

        $event->getRequest()->setLocale($this->locales[0]);
    }
}
