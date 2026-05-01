# i18n & Conditional routing

The i18n system is designed to be **transparent**: a label using a single language gets no URL prefix at all. A multi-language label automatically gets the `/{_locale}` prefix on every route and a language switcher in the header.

## Configuration

The `APP_LOCALES` environment variable (in `.env`) drives all behaviour:

```dotenv
# Mono-locale: no URL prefix (/albums, /artists…)
APP_LOCALES=fr

# Multi-locale: prefixed URLs (/fr/albums, /en/albums…)
APP_LOCALES=fr,en
```

The **first locale** is always the default. It is also set in `config/services.yaml` via the `app.default_locale` parameter (defaults to `fr`).

## Mono-locale mode

- No prefix in URLs
- No language switcher in the header
- Locale is forced by `LocaleSubscriber` on every request

```
/           → HomeController::index()
/albums     → AlbumController::index()
/album/slug → AlbumController::show()
```

## Multi-locale mode

- All front routes are prefixed with `/{_locale}`
- The requirement `fr|en` (or the configured list) is added automatically
- The default locale is used as the default value for the `_locale` parameter
- `/` redirects (302) to `/{default_locale}/`
- The language switcher is visible in the header

```
/           → 302 → /fr/
/fr/        → HomeController::index() (locale: fr)
/en/        → HomeController::index() (locale: en)
/fr/albums  → AlbumController::index() (locale: fr)
```

## Key files

### `src/Routing/FrontRouteLoader.php`

Service tagged `routing.loader`. Reads `%app.locales%` and loads routes from `src/Controller/Front/` via the PHP attribute loader. In multi-locale mode, calls `addPrefix('/{_locale}')` on the whole `RouteCollection` and adds the root redirect route.

Front controllers have **no** locale prefix in their `#[Route]` attributes — the loader handles that.

```php
// Correct pattern
#[Route('/albums', name: 'front_album_index')]
public function index(): Response { ... }

// Result in multi-locale : /{_locale}/albums
// Result in mono-locale  : /albums
```

### `src/EventListener/LocaleSubscriber.php`

Listens on `kernel.request` (priority 20). In mono-locale mode (`count($locales) === 1`), calls `$request->setLocale()` on every main request. In multi-locale mode it is a no-op — the locale is already provided by the route parameter.

## Adding a language

1. Add the locale in `.env`: `APP_LOCALES=fr,en,de`
2. Create the translation file: `translations/messages+intl-icu.de.yaml`
3. Clear the cache: `castor docker:builder -- php bin/console cache:clear`

Routes and the language switcher update automatically.

## Switching from multi to mono-locale

1. `.env` → `APP_LOCALES=fr`
2. Clear the cache
3. Existing URLs with a prefix (`/fr/albums`) will return 404 — set up redirects if the site is already live.

## Translations

Translation files use the **ICU** format (`messages+intl-icu.{locale}.yaml`) to support plurals and date/number formatting.

```yaml
# translations/messages+intl-icu.en.yaml
nav:
    albums: Albums
footer:
    copyright: "© {year} Indie Label Shop"
```

In Twig templates:
```twig
{{ 'nav.albums'|trans }}
{{ 'footer.copyright'|trans({year: 'now'|date('Y')}) }}
```

## Multilingual SEO

In multi-locale mode, `front/layout.html.twig` automatically injects `<link rel="alternate" hreflang>` tags in `<head>` for every configured locale, plus a `hreflang="x-default"` tag pointing to the default locale.
