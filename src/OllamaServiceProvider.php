<?php

namespace Cloudstudio\Ollama;

use Cloudstudio\Ollama\Services\ModelService;
use Illuminate\Foundation\Application;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OllamaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('ollama-laravel')
            ->hasConfigFile();
    }

    /**
     * Method register
     *
     * @return void
     * @throws InvalidPackage
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(ModelService::class, function () {
            return new ModelService();
        });

        $this->app->singleton(Ollama::class, function (Application $app) {
            return new Ollama(
                $app->make(ModelService::class)
            );
        });
    }
}
