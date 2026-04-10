<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
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

    public function test_create_checkout_preserves_regional_pricing_fields_for_direct_charge_flows(): void
    {
        Http::fake([
            'test-api.creem.io/v1/checkouts' => Http::response([
                'id' => 'checkout_inr_123',
                'checkout_url' => 'https://checkout.creem.io/checkout_inr_123',
                'status' => 'pending',
                'currency' => 'INR',
                'amount' => 49900,
                'price_id' => 'price_inr_monthly',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CheckoutService($client);

        $payload = [
            'price_id' => 'price_inr_monthly',
            'currency' => 'INR',
            'amount' => 49900,
            'customer_id' => 'cust_123',
            'payment_method_id' => 'pm_saved_123',
            'off_session' => true,
        ];

        $result = $service->create($payload);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->method() === 'POST'
                && str_contains($request->url(), '/checkouts')
                && ($data['price_id'] ?? null) === 'price_inr_monthly'
                && ($data['currency'] ?? null) === 'INR'
                && ($data['amount'] ?? null) === 49900
                && ($data['customer_id'] ?? null) === 'cust_123'
                && ($data['payment_method_id'] ?? null) === 'pm_saved_123'
                && ($data['off_session'] ?? null) === true
                && ! array_key_exists('product_id', $data);
        });

        $this->assertSame('INR', $result['currency']);
        $this->assertSame(49900, $result['amount']);
        $this->assertSame('price_inr_monthly', $result['price_id']);
    }

    public function test_create_checkout_exposes_provider_currency_mismatch_without_local_rewrite(): void
    {
        Http::fake([
            'test-api.creem.io/v1/checkouts' => Http::response([
                'id' => 'checkout_fx_123',
                'checkout_url' => 'https://checkout.creem.io/checkout_fx_123',
                'status' => 'pending',
                'currency' => 'EUR',
                'amount' => 49900,
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CheckoutService($client);

        $result = $service->create([
            'price_id' => 'price_inr_monthly',
            'currency' => 'INR',
            'amount' => 49900,
        ]);

        $this->assertSame('EUR', $result['currency']);
        $this->assertSame(49900, $result['amount']);
    }

    public function test_create_checkout_rejects_product_and_price_ids_together(): void
    {
        $client = CreemClient::fromProfile('default');
        $service = new CheckoutService($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provide either product_id or price_id for checkout creation, not both.');

        $service->create([
            'product_id' => 'prod_123',
            'price_id' => 'price_inr_monthly',
        ]);
    }

    public function test_create_checkout_requires_explicit_price_for_direct_charge_flows(): void
    {
        $client = CreemClient::fromProfile('default');
        $service = new CheckoutService($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Direct or off-session checkout creation must use an explicit price_id instead of product_id to avoid regional pricing mismatches.');

        $service->create([
            'product_id' => 'prod_123',
            'payment_method_id' => 'pm_saved_123',
            'off_session' => true,
        ]);
    }
}
