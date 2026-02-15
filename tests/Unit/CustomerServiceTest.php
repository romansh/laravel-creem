<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\CustomerService;

class CustomerServiceTest extends TestCase
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

    public function test_can_find_customer()
    {
        Http::fake([
            'test-api.creem.io/v1/customers*' => Http::response([
                'id' => 'cust_123',
                'email' => 'test@example.com',
                'name' => 'John Doe',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CustomerService($client);

        $result = $service->find('cust_123');

        $this->assertEquals('cust_123', $result['id']);
        $this->assertEquals('test@example.com', $result['email']);
    }

    public function test_can_find_customer_by_email()
    {
        Http::fake([
            'test-api.creem.io/v1/customers*' => Http::response([
                'id' => 'cust_123',
                'email' => 'test@example.com',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CustomerService($client);

        $result = $service->findByEmail('test@example.com');

        $this->assertEquals('test@example.com', $result['email']);
    }

    public function test_can_list_customers()
    {
        Http::fake([
            'test-api.creem.io/v1/customers/list*' => Http::response([
                'items' => [
                    ['id' => 'cust_1', 'email' => 'user1@example.com'],
                    ['id' => 'cust_2', 'email' => 'user2@example.com'],
                ],
                'pagination' => [
                    'total_records' => 2,
                    'total_pages' => 1,
                    'current_page' => 1,
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CustomerService($client);

        $result = $service->list();

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(2, $result['items']);
    }

    public function test_all_alias_calls_list()
    {
        Http::fake([
            'test-api.creem.io/v1/customers/list*' => Http::response([
                'items' => [
                    ['id' => 'cust_1', 'email' => 'user1@example.com'],
                ],
                'pagination' => ['total_records' => 1, 'total_pages' => 1, 'current_page' => 1],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CustomerService($client);

        $result = $service->all();

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
    }

    public function test_can_create_portal_link()
    {
        Http::fake([
            'test-api.creem.io/v1/customers/billing' => Http::response([
                'customer_portal_link' => 'https://portal.creem.io/abc123',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new CustomerService($client);

        $result = $service->createPortalLink('cust_123');

        $this->assertEquals('https://portal.creem.io/abc123', $result);
    }
}
