<?php

use Castor\Attribute\AsTask;

use function Castor\guard_min_version;
use function Castor\import;
use function Castor\io;
use function Castor\notify;
use function Castor\variable;
use function docker\about;
use function docker\build;
use function docker\docker_compose_run;
use function docker\up;

// use function docker\workers_start;
// use function docker\workers_stop;

guard_min_version('0.26.0');

import(__DIR__ . '/.castor');

/**
 * @return array{project_name: string, root_domain: string, extra_domains: string[], php_version: string}
 */
function create_default_variables(): array
{
    $projectName = 'indie-label-shop';
    $tld = 'local';

    return [
        'project_name' => $projectName,
        'root_domain' => "{$projectName}.{$tld}",
        'mail_domain' => "mail.{$projectName}.{$tld}",
        'extra_domains' => [
            "www.{$projectName}.{$tld}",
        ],
        // In order to test docker stater, we need a way to pass different values.
        // You should remove the `$_SERVER` and hardcode your configuration.
        'php_version' => $_SERVER['DS_PHP_VERSION'] ?? '8.5',
        'registry' => $_SERVER['DS_REGISTRY'] ?? null,
    ];
}

#[AsTask(description: 'Builds and starts the infrastructure, then install the application (composer, yarn, ...)')]
function start(): void
{
    io()->title('Starting the stack');

    // workers_stop();
    build();
    install();
    up(profiles: ['default']); // We can't start worker now, they are not installed
    migrate();
    initialize_data();
    // workers_start();

    notify('The stack is now up and running.');
    io()->success('The stack is now up and running.');

    about();
}

#[AsTask(description: 'Installs the application (composer, yarn, ...)', namespace: 'app', aliases: ['install'])]
function install(): void
{
    io()->title('Installing the application');

    $basePath = sprintf('%s/application', variable('root_dir'));

    if (is_file("{$basePath}/composer.json")) {
        io()->section('Installing PHP dependencies');
        docker_compose_run('composer install -n --prefer-dist --optimize-autoloader');
    }
    if (is_file("{$basePath}/yarn.lock")) {
        io()->section('Installing Node.js dependencies');
        docker_compose_run('yarn install --frozen-lockfile');
    } elseif (is_file("{$basePath}/package.json")) {
        io()->section('Installing Node.js dependencies');

        if (is_file("{$basePath}/package-lock.json")) {
            docker_compose_run('npm ci');
        } else {
            docker_compose_run('npm install');
        }
    }
    if (is_file("{$basePath}/importmap.php")) {
        io()->section('Installing importmap');
        docker_compose_run('bin/console importmap:install');
    }

    qa\install();
}

#[AsTask(description: 'Clears the application cache', namespace: 'app', aliases: ['cache-clear'])]
function cache_clear(bool $warm = true): void
{
    // io()->title('Clearing the application cache');

    // docker_compose_run('rm -rf var/cache/');

    // if ($warm) {
    //     cache_warmup();
    // }
}

#[AsTask(description: 'Warms the application cache', namespace: 'app', aliases: ['cache-warmup'])]
function cache_warmup(): void
{
    // io()->title('Warming the application cache');

    // docker_compose_run('bin/console cache:warmup', c: context()->withAllowFailure());
}

#[AsTask(description: 'Migrates database schema', namespace: 'app:db', aliases: ['migrate'])]
function migrate(): void
{
    io()->title('Migrating the database schema');

    docker_compose_run('bin/console doctrine:database:create --if-not-exists');
    docker_compose_run('bin/console doctrine:migration:migrate -n --allow-no-migration --all-or-nothing');
}

#[AsTask(description: 'Initializes base data if needed', namespace: 'app:db')]
function initialize_data(): void
{
    $res = docker_compose_run('bin/console doctrine:query:sql "SELECT COUNT(*) FROM sylius_country" | grep -oE "[0-9]+" | head -1');
    $count = (int) $res->getOutput();

    if (0 === $count) {
        io()->section('Initializing base data (countries and zones)');
        docker_compose_run('bin/console doctrine:fixtures:load --group=base -n --append');
        docker_compose_run('bin/console aropixel:admin:create-user -n');
    }
}

#[AsTask(description: 'Loads dev fixtures', namespace: 'app:db', aliases: ['fixture'])]
function fixtures(): void
{
    io()->title('Loads dev fixtures');

    docker_compose_run('bin/console doctrine:fixtures:load --group=base -n');
    docker_compose_run('bin/console aropixel:admin:create-user -n');
    docker_compose_run('bin/console doctrine:fixtures:load --group=dev -n --append');
}
