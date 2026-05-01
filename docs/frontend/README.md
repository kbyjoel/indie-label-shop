# Frontend — Indie Label Shop

Documentation for the public-facing storefront (visitor/customer side). The back-office (admin) is documented separately.

## Stack

| Layer | Technology |
|---|---|
| Templates | Twig 3 (3-level inheritance) |
| CSS | Tailwind CSS v4 via `symfonycasts/tailwind-bundle` |
| Interactivity | Stimulus 3 |
| SPA navigation | Turbo Drive 7 |
| Assets | Symfony Asset Mapper |
| i18n | Symfony Translator + ICU (conditional mono/multi-locale) |

## Directory structure

```
application/
  src/
    Controller/Front/     # Front controllers (one per functional domain)
    Routing/
      FrontRouteLoader.php # Conditional /{_locale} routing
    EventListener/
      LocaleSubscriber.php # Forces locale in mono-locale mode
  templates/
    base.html.twig          # Base HTML skeleton
    front/
      layout.html.twig      # Main layout (header, footer, blocks)
      partials/             # Reusable components
      home/                 # Homepage
      band/                 # Artist pages
      album/                # Album pages
      product/              # Merch shop
      cart/                 # Cart
      checkout/             # Checkout flow
      account/              # Customer account
  assets/
    styles/app.css          # Tailwind + blank theme
    controllers/            # Front Stimulus controllers
  translations/
    messages+intl-icu.fr.yaml
    messages+intl-icu.en.yaml
```

## Useful commands

```bash
# Build Tailwind CSS (required after modifying app.css or templates)
castor docker:builder -- php bin/console tailwind:build

# Watch mode (active development)
castor docker:builder -- php bin/console tailwind:build --watch

# Inspect front routes
castor docker:builder -- php bin/console debug:router | grep front_

# Clear cache
castor docker:builder -- php bin/console cache:clear
```

## Implementation steps

| Step | Content | Documentation |
|---|---|---|
| Step 1 | Layout, Tailwind, conditional i18n | [i18n-routing.md](i18n-routing.md) · [blank-theme.md](blank-theme.md) |
| Step 2 | Music catalogue (albums, artists) | [catalogue.md](catalogue.md) |
| Step 3 | Album page + WaveSurfer audio player | — |
| Step 4 | Merch shop, custom cart | — |
| Step 5 | Checkout (address, shipping) + customer account | — |
| Step 6 | Stripe & PayPal payments + webhooks | — |
| Step 7 | Digital file downloads | — |
| Step 8 | Post-purchase transactional emails | — |
