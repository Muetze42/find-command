<?php

namespace NormanHuth\FindCommand;

use Composer\InstalledVersions;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\AboutCommand;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * The composer package name.
     */
    protected string $package = 'norman-huth/find-command';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([LaravelFindCommand::class]);
            $this->addAbout();
        }
    }

    /**
     * Add additional data to the output of the 'about' command.
     */
    protected function addAbout(): void
    {
        $version = InstalledVersions::isInstalled($this->package) ? InstalledVersions::getVersion($this->package) : 'Unknown';

        AboutCommand::add($this->package, fn () => ['Version' => $version]);
    }
}
