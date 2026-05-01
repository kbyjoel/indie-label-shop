# Indie Label Shop

A self-hosted e-commerce platform for independent music labels. Sell albums, releases, and merch — manage your catalog from a built-in admin, let customers download purchased files directly from their account.

Built on Symfony 7.4 and Sylius Core 2.2. Designed to be cloned, branded, and deployed by any label or their developer.

---

## What you get

**For the label (admin)**
- Artist (band) and album management with tracklists and artwork
- Automatic MP3 preview encoding on upload (128 kbps + waveform)
- Merch products with variants (size, colour…)
- Orders, customers, shipping methods, tax rates, promotions
- Stripe and PayPal payment gateways (configured via admin, no code change needed)

**For the customer (storefront)**
- Music catalogue with audio previews (WaveSurfer waveform player)
- Merch shop with variant selection
- Custom cart and checkout flow (no SyliusShopBundle)
- Post-purchase digital downloads (MP3 320 kbps, WAV, ZIP) generated on demand
- Customer account with order history and download access
- Conditional multilingual support (single-locale or multi-locale URLs)

---

## Requirements

- [Docker](https://www.docker.com/) and Docker Compose
- [Castor](https://castor.jolicode.com/install/) (PHP task runner, installed on the host)

---

## Quick start

```bash
# 1. Clone the repository
git clone https://github.com/your-org/indie-label-shop.git my-label-shop
cd my-label-shop

# 2. Start the stack, install dependencies, run migrations and load fixtures
castor start

# 3. Build the frontend CSS
castor docker:builder -- php bin/console tailwind:build
```

The application is available at **`https://indie-label-shop.local`** (the domain is configurable in `castor.php`).

---

## Configuration

After the first install, the three things to set up in `.env`:

```dotenv
# Admin URL prefix — change this to something secret before going live
ADMIN_PATH=replace-this-path

# Supported locales (comma-separated). First value is the default.
# Single locale → no /{locale} prefix in URLs.
# Multiple locales → prefixed URLs + language switcher in the header.
APP_LOCALES=fr

# Payment webhook secrets (see docs for Stripe/PayPal setup)
STRIPE_WEBHOOK_SECRET=
PAYPAL_WEBHOOK_ID=
```

---

## Branding & theme

The storefront uses a **blank theme** driven by CSS custom properties. To apply your label's colours and typography, edit `assets/styles/app.css` — no template changes needed:

```css
@theme {
    --color-accent:    #your-brand-color;
    --font-display:    'Your Font', ui-sans-serif, system-ui, sans-serif;
}
```

Then rebuild:

```bash
castor docker:builder -- php bin/console tailwind:build
```

See [docs/frontend/blank-theme.md](docs/frontend/blank-theme.md) for the full variable reference.

---

## Development commands

| Task | Command |
|---|---|
| Start infrastructure | `castor up` |
| Stop infrastructure | `castor stop` |
| Install dependencies | `castor app:install` |
| Run migrations | `castor app:db:migrate` |
| Load fixtures | `castor app:db:fixture` |
| Clear cache | `castor app:cache-clear` |
| Build CSS | `castor docker:builder -- php bin/console tailwind:build` |
| Watch CSS | `castor docker:builder -- php bin/console tailwind:build --watch` |
| Fix code style | `castor qa:cs` |
| Static analysis | `castor qa:phpstan` |
| Run tests | `castor qa:phpunit` |

---

## Documentation

- [Frontend overview](docs/frontend/README.md) — stack, directory structure, commands
- [i18n & conditional routing](docs/frontend/i18n-routing.md) — mono/multi-locale setup
- [Blank theme & customisation](docs/frontend/blank-theme.md) — colours, fonts, Tailwind build

---

## Project structure

```
application/     Symfony/Sylius application source code
infrastructure/  Docker configuration and services
castor.php       Automation task definitions
docs/            Project documentation
```

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

---

Project powered by :
- [Symfony](https://symfony.com/)
- [Aropixel Admin](https://github.com/aropixel/aropixel-admin-bundle)
- [JoliCode Docker Starter](https://github.com/jolicode/docker-starter)
- [Castor](https://castor.jolicode.com/)
- [Sylius](https://sylius.com/) 
- [Tailwind CSS](https://tailwindcss.com/)