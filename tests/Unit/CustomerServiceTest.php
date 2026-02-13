<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\CustomerService;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class CustomerServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['creem.profiles.default' => [
            'api_key' => 'test_api_key',
            'test_mode' => true,
        ]]);
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
