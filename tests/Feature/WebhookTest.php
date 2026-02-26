<?php

namespace Romansh\LaravelCreem\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Events\CheckoutCompleted;
use Romansh\LaravelCreem\Events\GrantAccess;

class WebhookTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CreemServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['creem.profiles.default' => [
            'api_key' => 'test_api_key',
            'test_mode' => true,
            'webhook_secret' => 'test_webhook_secret',
        ]]);
    }

    public function test_webhook_verifies_signature()
    {
        $payload = json_encode([
            'eventType' => 'checkout.completed',
            'data' => ['id' => 'checkout_123'],
        ]);

        $signature = hash_hmac('sha256', $payload, 'test_webhook_secret');

        $response = $this->postJson('/creem/webhook', json_decode($payload, true), [
            'creem-signature' => $signature,
        ]);

        $response->assertStatus(200);
    }

    public function test_webhook_rejects_invalid_signature()
    {
        $payload = [
            'eventType' => 'checkout.completed',
            'data' => ['id' => 'checkout_123'],
        ];

        $response = $this->postJson('/creem/webhook', $payload, [
            'creem-signature' => 'invalid_signature',
        ]);

        $response->assertStatus(403);
    }

    public function test_webhook_rejects_missing_signature()
    {
        $payload = [
            'eventType' => 'checkout.completed',
            'data' => ['id' => 'checkout_123'],
        ];

        $response = $this->postJson('/creem/webhook', $payload);

        $response->assertStatus(401);
    }

    public function test_webhook_dispatches_checkout_completed_event()
    {
        Event::fake();

        $payload = json_encode([
            'eventType' => 'checkout.completed',
            'data' => ['id' => 'checkout_123'],
        ]);

        $signature = hash_hmac('sha256', $payload, 'test_webhook_secret');

        $this->postJson('/creem/webhook', json_decode($payload, true), [
            'creem-signature' => $signature,
        ]);

        Event::assertDispatched(CheckoutCompleted::class);
        Event::assertDispatched(GrantAccess::class);
    }
}
