[![Build Status](https://travis-ci.org/gabeta/lara-pnn.svg?branch=main)](https://travis-ci.org/gabeta/lara-pnn)

# Lara-pnn
Lara-pnn is a laravel package which allows you to format your phone number
in the new Ivorian format (change from 8 digits to 10 digits).
>**Note:**
> 
>From January 31, 2021, Ivorian numbers will change to 10 digits, ARTCI has published a note to help with migration.
> 
> This package will be useful for existing applications with an Ivorian number database. Ivory Coast Diald code is **+225**

## Installation
Require this package with composer.
```shell
composer require gabeta/lara-pnn
```
Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

##### Laravel without auto-discovery:
If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
Gabeta\LaraPnn\laraPnnServiceProvider::class
```
And add this to your facades in app.php:

```php
'LaraPnn' => Gabeta\LaraPnn\Facades\LaraPnn::class,
```

The package was designed for the Ivorian case but if you have a similar case
other than that of Côte d'Ivoire. This package handled it very well, you just have to publish and
modify the package configuration file. See more options in `config/larapnn.php`

##### Copy the package config to your local config with the publish command:

```shell
php artisan vendor:publish --provider="Gabeta\LaraPnn\laraPnnServiceProvider"
```

## Usage

#### Prepare your model

So that your models can format your Ivorian numbers, the model must implement the following interface and trait:
```php
use Gabeta\LaraPnn\InteractWithLaraPnn;
use Gabeta\LaraPnn\LaraPnnAbstract;

class YourModel extends Model implements LaraPnnAbstract
{
    use InteractWithLaraPnn;
}
```

Then you must define the fields concerned by the migration.
```php
use Gabeta\LaraPnn\InteractWithLaraPnn;
use Gabeta\LaraPnn\LaraPnnAbstract;

class YourModel extends Model implements LaraPnnAbstract
{
    use InteractWithLaraPnn;

    protected $pnnFields = [
        'mobile' => ['mobile_field_name'],
        'fix' => ['fix_field_name']
    ];
}
```

We check the eligibility of the number before migration based on the dial code
if the dial code value is in another field you must define with attribute `$pnnDialCodeFields`:

```php
use Gabeta\LaraPnn\InteractWithLaraPnn;
use Gabeta\LaraPnn\LaraPnnAbstract;

class YourModel extends Model implements LaraPnnAbstract
{
    use InteractWithLaraPnn;

    protected $pnnFields = [
        'mobile' => ['mobile_field_name'],
        'fix' => ['fix_field_name']
    ];
    
    protected $pnnDialCodeFields = [
        'mobile_field_name' => 'mobile_dial_code_field_name',
        'fix_field_name' => 'fix_dial_code_field_name'
    ];
}
```
  

#### Basic usage: Migrate without change database value
You can make a basic use of it which will migrate your numbers without modifying the values ​​in the database.

```php

// Before use LaraPnn trait
$yourModel->mobile_field_name // 225 09 00 00 00 
$yourModel->fix_field_name // 225 20 30 00 00 

// After use LaraPnn trait
$yourModel->mobile_field_name // 225 07 09 00 00 00  
$yourModel->fix_field_name // 225 27 20 30 00 00 

```

#### Advanced usage: Database migration
For a migration of numbers in the database, the one-command package allows you to migrate all your numbers from a single mode.

```shell
php artisan larapnn:migrate YouModelNamepace\\YourModel
```

You also have a command that allows you to rollback

```shell
php artisan larapnn:rollback YouModelNamepace\\YourModel
```
