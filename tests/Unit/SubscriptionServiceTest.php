<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\SubscriptionService;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class SubscriptionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['creem.profiles.default' => [
            'api_key' => 'test_api_key',
            'test_mode' => true,
        ]]);
    }

    public function test_can_find_subscription()
    {
        Http::fake([
            'test-api.creem.io/v1/subscriptions*' => Http::response([
                'id' => 'sub_123',
                'status' => 'active',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new SubscriptionService($client);

        $result = $service->find('sub_123');

        $this->assertEquals('sub_123', $result['id']);
        $this->assertEquals('active', $result['status']);
    }

    public function test_can_cancel_subscription()
    {
        Http::fake([
            'test-api.creem.io/v1/subscriptions/*/cancel' => Http::response([
                'id' => 'sub_123',
                'status' => 'canceled',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new SubscriptionService($client);

        $result = $service->cancel('sub_123');

        $this->assertEquals('canceled', $result['status']);
    }

    public function test_can_pause_subscription()
    {
        Http::fake([
            'test-api.creem.io/v1/subscriptions/*/pause' => Http::response([
                'id' => 'sub_123',
                'status' => 'paused',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new SubscriptionService($client);

        $result = $service->pause('sub_123');

        $this->assertEquals('paused', $result['status']);
    }

    public function test_can_resume_subscription()
    {
        Http::fake([
            'test-api.creem.io/v1/subscriptions/*/resume' => Http::response([
                'id' => 'sub_123',
                'status' => 'active',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new SubscriptionService($client);

        $result = $service->resume('sub_123');

        $this->assertEquals('active', $result['status']);
    }

    public function test_can_upgrade_subscription()
    {
        Http::fake([
            'test-api.creem.io/v1/subscriptions/*/upgrade' => Http::response([
                'id' => 'sub_123',
                'product' => ['id' => 'prod_456'],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new SubscriptionService($client);

        $result = $service->upgrade('sub_123', 'prod_456');

        $this->assertEquals('prod_456', $result['product']['id']);
    }
}
