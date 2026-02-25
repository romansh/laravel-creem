<?php

namespace Romansh\LaravelCreem;

use Illuminate\Support\ServiceProvider;
use Romansh\LaravelCreem\Console\Commands\TestWebhookCommand;

/**
 * Creem Laravel service provider.
 */
class CreemServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/creem.php',
            'creem'
        );

        $this->app->singleton('creem', function () {
            return Creem::make();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Skip booting during package discovery to avoid cache path issues
        if (! $this->app->bound('path.config')) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/creem.php' => config_path('creem.php'),
            ], 'creem-config');

            $this->commands([
                TestWebhookCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');
    }
}
