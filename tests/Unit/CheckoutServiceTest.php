<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\CheckoutService;

class CheckoutServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['creem.profiles.default' => [
            'api_key' => 'test_api_key',
            'test_mode' => true,
            'webhook_secret' => 'test_webhook_secret',
        ]]);
    }

    protected function getPackageProviders($app)
    {
        return [
            CreemServiceProvider::class,
        ];
    }

    public function test_can_create_checkout()
    {
        Http::fake([
            'test-api.creem.io/v1/checkouts' => Http::response([
                'id' => 'checkout_123',
                'checkout_url' => 'https://checkout.creem.io/checkout_123',
                'status' => 'pending',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CheckoutService($client);

        $result = $service->create([
            'product_id' => 'prod_123',
            'success_url' => 'https://example.com/success',
        ]);

        $this->assertEquals('checkout_123', $result['id']);
        $this->assertArrayHasKey('checkout_url', $result);
    }

    public function test_can_find_checkout()
    {
        Http::fake([
            'test-api.creem.io/v1/checkouts*' => Http::response([
                'id' => 'checkout_123',
                'checkout_url' => 'https://checkout.creem.io/checkout_123',
                'status' => 'completed',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CheckoutService($client);

        $result = $service->find('checkout_123');

        $this->assertEquals('checkout_123', $result['id']);
        $this->assertEquals('completed', $result['status']);
    }
}
