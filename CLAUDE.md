# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Indie Label Shop** is a Symfony 7.4 / Sylius 2.2 e-commerce platform for independent music labels. It manages music catalogs (artists, bands, albums, releases, tracks) with automatic MP3 encoding and a custom admin interface.

## Development Commands

All tasks use **Castor** (PHP-based task runner). Run from the project root.

```bash
# Infrastructure
castor start          # First-time setup: build Docker images, install deps, run migrations
castor up             # Start Docker infrastructure
castor stop           # Stop Docker infrastructure
castor ssh            # Shell into the PHP container

# Application
castor app:install           # Install Composer/Node dependencies
castor app:db:migrate        # Run database migrations
castor app:db:fixture        # Load fixtures
castor app:cache-clear       # Clear Symfony cache

# Code Quality
castor qa:cs                 # Fix PHP coding standards (PHP CS Fixer)
castor qa:cs --dry-run       # Check without fixing
castor qa:phpstan            # Static analysis (PHPStan level 8)
castor qa:twig-cs            # Check Twig code style
castor qa:twig-cs --fix      # Fix Twig style issues
castor qa:phpunit            # Run unit tests
```

## Architecture

### Stack
- **Backend:** PHP 8.4+, Symfony 7.4, Sylius Core 2.2
- **Database:** MySQL via Doctrine ORM 3.6
- **Frontend:** Twig templates, Stimulus 3, Turbo 7, Asset Mapper
- **Admin:** Aropixel Admin Bundle (admin route prefix: `/philosophy-club-band`)
- **Async:** Symfony Messenger with Doctrine queue (used for MP3 encoding)
- **Storage:** Flysystem (local/S3)
- **Infrastructure:** Docker via JoliCode Docker Starter

### Source Layout (`application/src/`)

| Directory | Role |
|-----------|------|
| `Entity/` | Doctrine entities — implements Sylius interfaces (Product, Order, Customer, Channel, etc.) plus music-domain entities (Band, Album, Release, Track, Tracklist, Artist) |
| `Controller/Admin/` | Admin controllers built on Aropixel Admin Bundle |
| `Repository/` | Custom Doctrine repositories |
| `Form/` | Symfony form types |
| `Component/Track/` | Domain component for audio tracks: async MP3 encoding via FFmpeg |
| `EventListener/` | Doctrine and Symfony event listeners |
| `Command/` | Console commands (legacy data import) |
| `DataFixtures/` | PHPUnit/dev fixtures |

### Sylius Integration

Sylius entities are resolved through custom implementations in `src/Entity/`. The app extends core Sylius resources (Product, ProductVariant, Order, Payment, Address, Channel, Customer, Shipment, ShippingMethod, Zone, ZoneMember, TaxCategory, TaxRate, etc.). Configuration is in `config/packages/sylius_*.yaml`.

### Async MP3 Encoding

When a master audio file is uploaded, a `EncodeTrackMp3Message` is dispatched via Symfony Messenger. The handler (`src/Component/Track/MessageHandler/EncodeTrackMp3Handler`) processes it using PHP-FFmpeg. The Doctrine-backed queue transport is configured in `config/packages/messenger.yaml`.

### Music Domain Entities

Core music-specific entities beyond Sylius: `Band`, `Album`, `Release`, `Track`, `Tracklist`, `TrackMasterFile`, `Artist`. Many support Doctrine translations (multi-language) via DoctrineExtensions. Image crop entities exist for Band, Album, and Release.

### Admin Interface

Admin is built on Aropixel Admin Bundle. Controllers in `src/Controller/Admin/` handle CRUD for all domain entities. Templates live in `templates/admin/`. The admin route prefix is configurable via `ADMIN_PATH` env var.

## Configuration Highlights

- **Services:** `config/services.yaml` — autowiring enabled, PSR-4 autoload `App\` → `src/`
- **Doctrine mappings:** `config/packages/doctrine.yaml`
- **Asset imports:** `importmap.php`
- **Queue routing:** `config/packages/messenger.yaml`
- **Static analysis:** `phpstan.neon` (level 8, with Doctrine and Symfony extensions)
- **Code style:** `.php-cs-fixer.php` (PHP 8.3 target, Symfony+PSR12 rules)
