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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/creem.php' => config_path('creem.php'),
            ], 'creem-config');

            $this->commands([
                TestWebhookCommand::class,
            ]);
            
            // Skip route loading during package discovery to prevent cache initialization errors
            if ($this->isPackageDiscovery()) {
                return;
            }
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');
    }

    /**
     * Check if we're running package:discover command.
     */
    protected function isPackageDiscovery(): bool
    {
        return isset($_SERVER['argv']) && 
               in_array('package:discover', $_SERVER['argv'], true);
    }
}
