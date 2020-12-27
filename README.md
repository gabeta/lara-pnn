# Lara-pnn
Lara-pnn is a laravel package which allows you to format your phone number
in the new Ivorian format (change from 8 digits to 10 digits).

> From January 31, 2021, Ivorian numbers will change to 10 digits, ARTCI has published a note to help with migration

## Installation
Require this package with composer.
```shell
composer require gabeta/lara-pnn
```
Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

#### Laravel without auto-discovery:
If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
Gabeta\LaraPnn\laraPnnServiceProvider::class
```

The package was designed for the Ivorian case but if you have a similar case
other than that of Côte d'Ivoire. This package handled it very well, you just have to publish and
modify the package configuration file.See more options in `config/larapnn.php`

#### Copy the package config to your local config with the publish command:

```shell
php artisan vendor:publish --provider="Gabeta\LaraPnn\laraPnnServiceProvider"
```

## Usage

