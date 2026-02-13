<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\ProductService;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class ProductServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['creem.profiles.default' => [
            'api_key' => 'test_api_key',
            'test_mode' => true,
        ]]);
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
            'test-api.creem.io/v1/products/create' => Http::response([
                'id' => 'prod_new',
                'name' => 'New Product',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new ProductService($client);

        $result = $service->create([
            'name' => 'New Product',
            'price' => 1000,
        ]);

        $this->assertEquals('prod_new', $result['id']);
    }
}
