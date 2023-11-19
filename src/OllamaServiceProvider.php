<?php

namespace Cloudstudio\Ollama;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Cloudstudio\Ollama\Commands\OllamaCommand;
use Cloudstudio\Ollama\Ollama;

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
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(OllamaService::class, function ($app) {
            return new Ollama();
        });
    }
}
