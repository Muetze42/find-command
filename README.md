# Find Command

A 3rd party command for the Frameworks [Symfony](https://symfony.com/) `^6.2|^7.0+` and [Laravel](https://laravel.com) `^10.0|^11.0+`.  
This command can be used to search for other commands in a Symfony or Laravel application.  
It uses [Laravel Prompts](https://laravel.com/docs/prompts) under the hoods.

![preview](https://raw.githubusercontent.com/Muetze42/find-command/main/docs/preview.gif)

## Install

```shell
composer require norman-huth/find-command
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

##### Except Commands

Add the method `exceptFromFindCommand` to the command:
```php
class MyCommand extends Command
{
    /**
     * Except this command from the find command.
     */
    public function exceptFromFindCommand(): bool
    {
        return true;
    }
```

```php
class MyCommand extends Command
{
    /**
     * Except this command from the find command.
     */
    public function exceptFromFindCommand(): bool
    {
        return config('app.env') != 'local';
    }
```

##### Search in the arguments and options descriptions too

```shell
php artisan find --deep
php bin/console find --deep
```

![preview](https://raw.githubusercontent.com/Muetze42/find-command/main/docs/preview-deep.gif)
