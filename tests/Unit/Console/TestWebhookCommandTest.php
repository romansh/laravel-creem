<?php

namespace Romansh\LaravelCreem\Tests\Unit\Console;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Http;
use Romansh\LaravelCreem\CreemServiceProvider;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;
use Romansh\LaravelCreem\Console\Commands\TestWebhookCommand;

class TestWebhookCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [CreemServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('creem.profiles.default', [
            'api_key' => 'test_key',
        ]);

        $app['config']->set('creem.webhook.path', '/test-webhook');
    }

    public function test_missing_webhook_secret_returns_failure()
    {
        config(['creem.profiles.default.webhook_secret' => null]);

        $this->artisan('creem:test-webhook')
            ->expectsOutput('Webhook secret not configured for profile: default')
            ->assertExitCode(1);
    }

    public function test_successful_webhook_returns_success()
    {
        config(['creem.profiles.default.webhook_secret' => 'secret']);

        Http::fake([
            '*' => Http::response('ok', 200),
        ]);

        $this->artisan('creem:test-webhook')
            ->assertExitCode(0);
    }

    public function test_failed_webhook_returns_failure_on_non_success()
    {
        config(['creem.profiles.default.webhook_secret' => 'secret']);

        Http::fake([
            '*' => Http::response('error', 500),
        ]);

        $this->artisan('creem:test-webhook')
            ->assertExitCode(1);
    }

    public function test_handle_via_command_tester_executes()
    {
        config(['creem.profiles.default.webhook_secret' => 'secret']);

        Http::fake([
            '*' => Http::response('ok', 200),
        ]);

        $application = new ConsoleApplication();
        $commandInstance = $this->app->make(TestWebhookCommand::class);
        $commandInstance->setLaravel($this->app);
        $application->add($commandInstance);

        $command = $application->find('creem:test-webhook');
        $tester = new CommandTester($command);

        $exit = $tester->execute(['event' => 'checkout.completed', '--profile' => 'default']);

        $this->assertEquals(0, $exit);
    }

    public function test_handle_catches_exception_and_returns_failure()
    {
        config(['creem.profiles.default.webhook_secret' => 'secret']);

        Http::fake(function () {
            throw new \Exception('boom');
        });

        $this->artisan('creem:test-webhook')
            ->assertExitCode(1);
    }
}
