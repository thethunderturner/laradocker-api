<?php

namespace TheThunderTurner\Docker;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TheThunderTurner\Docker\Commands\DockerCommand;

class DockerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laradocker-api')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laradocker_api_table')
            ->hasCommand(DockerCommand::class);
    }
}
