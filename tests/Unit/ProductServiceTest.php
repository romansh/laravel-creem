<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\ProductService;

class ProductServiceTest extends TestCase
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

    public function test_can_list_products()
    {
        Http::fake([
            'test-api.creem.io/v1/products/search*' => Http::response([
                'items' => [
                    ['id' => 'prod_1', 'name' => 'Product 1'],
                    ['id' => 'prod_2', 'name' => 'Product 2'],
                ],
                'pagination' => [
                    'total_records' => 2,
                    'total_pages' => 1,
                    'current_page' => 1,
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new ProductService($client);

        $result = $service->list();

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(2, $result['items']);
        $this->assertSame(2, $result['total']);
    }

    public function test_list_passes_filters_to_products_search(): void
    {
        Http::fake([
            'test-api.creem.io/v1/products/search*' => Http::response([
                'items' => [],
                'pagination' => ['total_records' => 0, 'total_pages' => 0, 'current_page' => 1],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new ProductService($client);

        $service->list(2, 15, [
            'status' => 'active',
            'billing_type' => 'recurring',
        ]);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_contains($request->url(), '/products/search')
                && ($data['status'] ?? null) === 'active'
                && ($data['billing_type'] ?? null) === 'recurring'
                && ($data['page_number'] ?? null) === 2
                && ($data['page_size'] ?? null) === 15;
        });
    }

    public function test_all_alias_calls_list()
    {
        Http::fake([
            'test-api.creem.io/v1/products/search*' => Http::response([
                'items' => [
                    ['id' => 'prod_1', 'name' => 'Product 1'],
                ],
                'pagination' => ['total_records' => 1, 'total_pages' => 1, 'current_page' => 1],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new ProductService($client);

        $result = $service->all();

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
    }

    public function test_can_find_product()
    {
        Http::fake([
            'test-api.creem.io/v1/products*' => Http::response([
                'id' => 'prod_123',
                'name' => 'Test Product',
                'price' => 1000,
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new ProductService($client);

        $result = $service->find('prod_123');

        $this->assertEquals('prod_123', $result['id']);
        $this->assertEquals('Test Product', $result['name']);
    }

    public function test_can_create_product()
    {
        Http::fake([
            'test-api.creem.io/v1/products' => Http::response([
                'id' => 'prod_new',
                'name' => 'New Product',
                'price' => 1000,
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new ProductService($client);

        $result = $service->create([
            'name' => 'New Product',
            'price' => 1000,
            'currency' => 'USD',
            'billing_type' => 'recurring',
        ]);

        $this->assertEquals('prod_new', $result['id']);
        $this->assertEquals('New Product', $result['name']);
    }
}
