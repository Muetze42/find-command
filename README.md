# Find Command

WIP

A 3rd party command for the Frameworks [Symfony](https://symfony.com/) and [Laravel](https://laravel.com).  
This command can be used to search for other commands in a Symfony or Laravel application.  
It uses [Laravel Prompts](https://laravel.com/docs/prompts) under the hoods.

![preview](docs/preview.gif)

## Dev Status

* Tested with Laravel 11
* Try to add this command as regular command: https://github.com/laravel/framework/pull/51379

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
