# Find Command

WIP

A 3rd party command for the Frameworks [Symfony](https://symfony.com/) `^6.2|^7.0+` and [Laravel](https://laravel.com) `^10.0|^11.0+`.  
This command can be used to search for other commands in a Symfony or Laravel application.  
It uses [Laravel Prompts](https://laravel.com/docs/prompts) under the hoods.

![preview](https://raw.githubusercontent.com/Muetze42/find-command/main/docs/preview.gif)

## Install

```shell
composer require norman-huth/find-command
```

For development and beta versions:

```shell
composer require norman-huth/find-command:"@dev"
```

### Usage

#### Laravel

Run the command.

```shell
php artisan find
```

#### Symfony

Register the `\NormanHuth\FindCommand\SymfonyFindCommand` in Your application and run the command.

```shell
php bin/console find
```

##### Search in the arguments and options descriptions too

```shell
php artisan find --deep
php bin/console find --deep
```

![preview](https://raw.githubusercontent.com/Muetze42/find-command/main/docs/preview-deep.gif)
