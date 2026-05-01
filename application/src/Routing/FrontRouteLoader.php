<?php

declare(strict_types=1);

namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Loads front-facing routes from src/Controller/Front/ and conditionally
 * prepends /{_locale} when more than one locale is configured.
 *
 * Mono-locale  (APP_LOCALES="fr")      → /albums, /artistes, …
 * Multi-locale (APP_LOCALES="fr en")   → /{_locale}/albums, … + redirect / → /fr/
 */
class FrontRouteLoader extends Loader
{
    private bool $loaded = false;

    /** @param list<string> $locales */
    public function __construct(
        private readonly array $locales,
        ?string $env = null,
    ) {
        parent::__construct($env);
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if ($this->loaded) {
            throw new \RuntimeException('The FrontRouteLoader has already been loaded.');
        }
        $this->loaded = true;

        $controllerDir = \dirname(__DIR__) . '/Controller/Front/';

        /** @var RouteCollection $frontRoutes */
        $frontRoutes = $this->import($controllerDir, 'attribute');

        $collection = new RouteCollection();

        if (\count($this->locales) > 1) {
            $localePattern = implode('|', array_map('preg_quote', $this->locales));

            $frontRoutes->addPrefix('/{_locale}');
            $frontRoutes->addRequirements(['_locale' => $localePattern]);
            $frontRoutes->addDefaults(['_locale' => $this->locales[0]]);

            // Redirect bare / → /{default_locale}/
            $redirect = new Route('/', [
                '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction',
                'path' => '/' . $this->locales[0] . '/',
                'permanent' => false,
            ]);
            $collection->add('front_locale_redirect', $redirect);
        }

        $collection->addCollection($frontRoutes);

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'front' === $type;
    }
}
