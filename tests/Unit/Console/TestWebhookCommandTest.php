<?php

namespace Romansh\LaravelCreem\Tests\Unit\Console;

use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;

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

    public function test_command_sends_documented_webhook_payload()
    {
        config(['creem.profiles.default.webhook_secret' => 'secret']);

        Http::fake([
            '*' => Http::response('ok', 200),
        ]);

        $this->artisan('creem:test-webhook', ['event' => 'subscription.paid'])
            ->assertExitCode(0);

        Http::assertSent(function (HttpRequest $request): bool {
            $payload = $request->data();

            return is_string($payload['id'])
                && str_starts_with($payload['id'], 'evt_')
                && $payload['eventType'] === 'subscription.paid'
                && is_int($payload['created_at'])
                && str_starts_with($payload['object']['id'], 'test_id_');
        });
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

        $this->artisan('creem:test-webhook', ['event' => 'checkout.completed', '--profile' => 'default'])
            ->assertExitCode(0);
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
