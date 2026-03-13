# Indie Label Shop

An e-commerce platform dedicated to independent music labels, allowing the management of artists (bands), albums, audio tracks (with automatic MP3 encoding), and merchandise.

## 🚀 Project Nature

**Indie Label Shop** is a modern "Online Shop" web application designed to simplify the sale of music and merchandise. The project is built on a solid foundation (Sylius Core) while offering a customized administration interface for smooth management of the music catalog.

## ✨ Key Features

- **Music Catalog Management**: Artists (bands), Albums, and Releases.
- **Track Management**: 
    - Support for tracklists.
    - Upload of Master files.
    - Automatic track encoding system into MP3 format (via Symfony Messenger).
- **Full E-commerce (Sylius Based)**:
    - Product and variant management.
    - Product options and attributes.
    - Cart and checkout system.
    - Customer and address management.
    - Multi-currency and multi-channel support.
- **Administration**: Powerful admin interface based on `aropixel/admin-bundle`.
- **Data Import**: CLI commands for importing historical data.

## 🛠 Technical Stack

- **Backend**: PHP 8.4+, Symfony 7.4.
- **E-commerce**: Sylius Core 2.2.
- **Database**: MySQL / MariaDB (via Doctrine ORM 3.6).
- **Administration**: Aropixel Admin Bundle.
- **Infrastructure**: Docker with JoliCode's `docker-starter`.
- **Automation**: [Castor](https://castor.jolicode.com/) for task and development environment management.

## 📦 Installation & Usage with Castor

This project uses **Castor**, a task runner written in PHP, to simplify interactions with the Docker infrastructure and Symfony commands.

### Prerequisites

- [Docker](https://www.docker.com/) and Docker Compose.
- [Castor](https://castor.jolicode.com/install/) installed on your host machine.

### Quick Start

To initialize the project, build Docker images, install dependencies, and prepare the database:

```bash
castor start
```

### Useful Commands

- **Start infrastructure**: `castor up`
- **Stop infrastructure**: `castor stop`
- **Install dependencies (Composer, Yarn/NPM)**: `castor install` (or `castor app:install`)
- **Run migrations**: `castor app:db:migrate`
- **Load fixtures**: `castor app:db:fixture`
- **Clear cache**: `castor app:cache-clear`
- **Access a container terminal**: `castor ssh` (via imported `.castor` tasks)

The application will be available by default at `https://indie-label-shop.local` (configurable in `castor.php`).

## 📁 Project Structure

- `application/`: Symfony/Sylius application source code.
- `infrastructure/`: Docker configuration and services.
- `castor.php` & `.castor/`: Automation task definitions.
- `tools/`: Additional development tools.

---
Project powered by [JoliCode Docker Starter](https://github.com/jolicode/docker-starter).