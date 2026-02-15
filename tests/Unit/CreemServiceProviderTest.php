<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\Console\Commands\TestWebhookCommand;
use Romansh\LaravelCreem\Creem;
use Romansh\LaravelCreem\CreemServiceProvider;

class CreemServiceProviderTest extends TestCase
{
    /**
     * Get package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [CreemServiceProvider::class];
    }

    /**
     * Define environment setup.
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('creem.profiles.default', [
            'api_key' => 'test_key',
        ]);
    }

    /**
     * Test that the singleton is registered and returns the correct instance.
     */
    public function test_it_registers_creem_singleton()
    {
        $this->assertTrue($this->app->bound('creem'));
        $this->assertInstanceOf(Creem::class, $this->app->make('creem'));
    }

    /**
     * Test that config is merged correctly.
     */
    public function test_it_merges_config()
    {
        $config = config('creem');

        $this->assertArrayHasKey('profiles', $config);
        $this->assertArrayHasKey('api_url', $config);
    }

    /**
    * Test that commands and publishing are registered when running in console.
    */
    public function test_it_registers_console_functionality()
    {
        // 1. Verify command is registered
        $commands = Artisan::all();
        $this->assertArrayHasKey('creem:test-webhook', $commands);
        $this->assertInstanceOf(TestWebhookCommand::class, $commands['creem:test-webhook']);

        // 2. Verify publishing tags exist
        $publishGroups = CreemServiceProvider::publishableGroups();
        $this->assertContains('creem-config', $publishGroups);

        // 3. Verify the specific config file is in the publish list
        $publishes = CreemServiceProvider::pathsToPublish(CreemServiceProvider::class, 'creem-config');

        // We look for a key that ends with 'config/creem.php' to avoid absolute path mismatches
        $found = false;
        foreach (array_keys($publishes) as $sourcePath) {
            if (str_ends_with(str_replace('\\', '/', $sourcePath), 'config/creem.php')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "The config file was not found in the publishing paths.");
    }
    /**
     * Test that routes are loaded.
     */
    public function test_it_loads_routes()
    {
        $routeCollection = $this->app['router']->getRoutes();

        // Adjust this to match the actual route defined in routes/webhooks.php
        // For example, checking if any route with 'creem' exists
        $hasWebhookRoute = false;
        foreach ($routeCollection as $route) {
            if (str_contains($route->uri(), 'creem') || str_contains($route->uri(), 'webhook')) {
                $hasWebhookRoute = true;
                break;
            }
        }

        $this->assertTrue($hasWebhookRoute, 'Creem webhook routes were not loaded.');
    }
}
