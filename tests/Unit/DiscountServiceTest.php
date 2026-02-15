<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\DiscountService;

class DiscountServiceTest extends TestCase
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

    public function test_can_create_percentage_discount()
    {
        Http::fake([
            'test-api.creem.io/v1/discounts' => Http::response([
                'id' => 'disc_123',
                'name' => 'Summer Sale',
                'code' => 'SUMMER50',
                'type' => 'percentage',
                'percentage' => 50,
                'status' => 'active',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new DiscountService($client);

        $result = $service->create([
            'name' => 'Summer Sale',
            'code' => 'SUMMER50',
            'type' => 'percentage',
            'percentage' => 50,
        ]);

        $this->assertEquals('disc_123', $result['id']);
        $this->assertEquals('SUMMER50', $result['code']);
        $this->assertEquals(50, $result['percentage']);
    }

    public function test_can_create_fixed_discount()
    {
        Http::fake([
            'test-api.creem.io/v1/discounts' => Http::response([
                'id' => 'disc_456',
                'name' => 'Welcome Bonus',
                'code' => 'WELCOME20',
                'type' => 'fixed',
                'amount' => 2000,
                'currency' => 'USD',
                'status' => 'active',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new DiscountService($client);

        $result = $service->create([
            'name' => 'Welcome Bonus',
            'code' => 'WELCOME20',
            'type' => 'fixed',
            'amount' => 2000,
            'currency' => 'USD',
        ]);

        $this->assertEquals('disc_456', $result['id']);
        $this->assertEquals('WELCOME20', $result['code']);
        $this->assertEquals(2000, $result['amount']);
    }

    public function test_can_find_discount_by_id()
    {
        Http::fake([
            'test-api.creem.io/v1/discounts*' => Http::response([
                'id' => 'disc_123',
                'code' => 'SUMMER50',
                'status' => 'active',
                'percentage' => 50,
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new DiscountService($client);

        $result = $service->find('disc_123');

        $this->assertEquals('disc_123', $result['id']);
        $this->assertEquals('SUMMER50', $result['code']);
    }

    public function test_can_find_discount_by_code()
    {
        Http::fake([
            'test-api.creem.io/v1/discounts*' => Http::response([
                'id' => 'disc_123',
                'code' => 'SUMMER50',
                'status' => 'active',
                'percentage' => 50,
                'redeem_count' => 25,
                'max_redemptions' => 100,
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new DiscountService($client);

        $result = $service->findByCode('SUMMER50');

        $this->assertEquals('SUMMER50', $result['code']);
        $this->assertEquals('active', $result['status']);
        $this->assertEquals(25, $result['redeem_count']);
    }

    public function test_can_delete_discount()
    {
        Http::fake([
            'test-api.creem.io/v1/discounts/*/delete' => Http::response([
                'id' => 'disc_123',
                'deleted' => true,
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new DiscountService($client);

        $result = $service->delete('disc_123');

        $this->assertTrue($result['deleted']);
    }
}
