---
name: castor-builder
description: >
  Run commands inside the project's PHP container via Castor.
  Use this skill to know how to run Symfony/Doctrine/Composer commands
  in the project's Docker environment.
---

# Skill: Running commands in the PHP container

## Basic command

```bash
castor builder <command>
```

All PHP/Symfony/Doctrine/Composer commands must be prefixed with `castor builder`.

## Common examples

```bash
# Doctrine migrations
castor builder php bin/console doctrine:migrations:diff
castor builder php bin/console doctrine:migrations:migrate --no-interaction

# Symfony cache
castor builder php bin/console cache:clear

# Generate an Aropixel CRUD
castor builder php bin/console aropixel:make:crud

# Composer
castor builder composer require vendor/package

# PHPStan / CS Fixer (or use the dedicated castor aliases)
castor builder vendor/bin/php-cs-fixer fix
castor builder vendor/bin/phpstan analyse
```

## Notes

- Do not use `castor ssh` (it does not exist). Always use `castor builder <command>`.
- `castor builder` uses TTY when available (interactive terminal), and automatically falls back to non-interactive mode otherwise (e.g. when running from Claude Code). This behaviour is handled in `.castor/docker.php`.
