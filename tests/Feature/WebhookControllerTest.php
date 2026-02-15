<?php

namespace Romansh\LaravelCreem\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Events\CheckoutCompleted;
use Romansh\LaravelCreem\Events\PaymentFailed;
use Romansh\LaravelCreem\Events\SubscriptionCanceled;
use Romansh\LaravelCreem\Events\SubscriptionCreated;

class WebhookControllerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CreemServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Конфиг для тестового webhook
        config(['creem.profiles.default' => [
            'api_key' => 'test_api_key',
            'test_mode' => true,
            'webhook_secret' => 'test_webhook_secret',
        ]]);
    }

    // Интеграционные тесты через HTTP-подобный запрос
    public function test_webhook_dispatches_checkout_completed_event()
    {
        Event::fake();

        $payload = ['event' => 'checkout.completed', 'data' => ['id' => 'checkout_123']];
        $signature = hash_hmac('sha256', json_encode($payload), 'test_webhook_secret');

        $response = $this->postJson('/creem/webhook', $payload, [
            'X-Creem-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        Event::assertDispatched(CheckoutCompleted::class);
    }

    public function test_webhook_dispatches_subscription_created_event()
    {
        Event::fake();

        $payload = ['event' => 'subscription.created', 'data' => ['id' => 'sub_123']];
        $signature = hash_hmac('sha256', json_encode($payload), 'test_webhook_secret');

        $this->postJson('/creem/webhook', $payload, [
            'X-Creem-Signature' => $signature,
        ]);

        Event::assertDispatched(SubscriptionCreated::class);
    }

    public function test_webhook_dispatches_subscription_canceled_event()
    {
        Event::fake();

        $payload = ['event' => 'subscription.canceled', 'data' => ['id' => 'sub_123']];
        $signature = hash_hmac('sha256', json_encode($payload), 'test_webhook_secret');

        $this->postJson('/creem/webhook', $payload, [
            'X-Creem-Signature' => $signature,
        ]);

        Event::assertDispatched(SubscriptionCanceled::class);
    }

    public function test_webhook_dispatches_payment_failed_event()
    {
        Event::fake();

        $payload = ['event' => 'payment.failed', 'data' => ['id' => 'txn_123']];
        $signature = hash_hmac('sha256', json_encode($payload), 'test_webhook_secret');

        $this->postJson('/creem/webhook', $payload, [
            'X-Creem-Signature' => $signature,
        ]);

        Event::assertDispatched(PaymentFailed::class);
    }

    // Unit-тесты контроллера напрямую
    public function test_logs_unhandled_event()
    {
        Log::spy();

        $controller = new \Romansh\LaravelCreem\Http\Controllers\WebhookController();
        $request = new Request(['event' => 'unknown.event', 'data' => []]);
        $controller($request);

        Log::shouldHaveReceived('info')
            ->with('Unhandled Creem webhook event: unknown.event', ['event' => 'unknown.event', 'data' => []]);
    }

    public function test_returns_400_if_event_missing()
    {
        $controller = new \Romansh\LaravelCreem\Http\Controllers\WebhookController();
        $response = $controller(new Request([]));

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['message' => 'Event type missing'], $response->getData(true));
    }

    // Проверка отклонения запроса с неверной подписью
    public function test_webhook_rejects_invalid_signature()
    {
        $payload = ['event' => 'checkout.completed', 'data' => ['id' => 'checkout_123']];
        $response = $this->postJson('/creem/webhook', $payload, [
            'X-Creem-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(403);
    }

    public function test_webhook_rejects_missing_signature()
    {
        $payload = ['event' => 'checkout.completed', 'data' => ['id' => 'checkout_123']];
        $response = $this->postJson('/creem/webhook', $payload);

        $response->assertStatus(401);
    }
}
