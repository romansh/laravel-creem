<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\TransactionService;

class TransactionServiceTest extends TestCase
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

    public function test_can_find_transaction()
    {
        Http::fake([
            'test-api.creem.io/v1/transactions*' => Http::response([
                'id' => 'txn_123',
                'amount' => 2000,
                'currency' => 'USD',
                'status' => 'completed',
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new TransactionService($client);

        $result = $service->find('txn_123');

        $this->assertEquals('txn_123', $result['id']);
        $this->assertEquals(2000, $result['amount']);
    }

    public function test_can_list_transactions()
    {
        Http::fake([
            'test-api.creem.io/v1/transactions/search*' => Http::response([
                'items' => [
                    ['id' => 'txn_1', 'amount' => 1000],
                    ['id' => 'txn_2', 'amount' => 2000],
                ],
                'pagination' => [
                    'total_records' => 2,
                    'total_pages' => 1,
                    'current_page' => 1,
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new TransactionService($client);

        $result = $service->list();

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(2, $result['items']);
        $this->assertSame(2, $result['total']);
    }

    public function test_all_alias_calls_list()
    {
        Http::fake([
            'test-api.creem.io/v1/transactions/search*' => Http::response([
                'items' => [
                    ['id' => 'txn_1', 'amount' => 1000],
                ],
                'pagination' => ['total_records' => 1, 'total_pages' => 1, 'current_page' => 1],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new TransactionService($client);

        $result = $service->all();

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
    }

    public function test_can_list_transactions_with_filters()
    {
        Http::fake([
            'test-api.creem.io/v1/transactions/search*' => Http::response([
                'items' => [
                    ['id' => 'txn_1', 'customer' => 'cust_123'],
                ],
                'pagination' => [
                    'total_records' => 1,
                    'total_pages' => 1,
                    'current_page' => 1,
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new TransactionService($client);

        $result = $service->list(['customer_id' => 'cust_123']);

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
        $this->assertSame(1, $result['total']);
    }

    public function test_list_normalizes_total_and_passes_arbitrary_filters(): void
    {
        Http::fake([
            'test-api.creem.io/v1/transactions/search*' => Http::response([
                'items' => [
                    ['id' => 'txn_1', 'amount' => 1000],
                ],
                'pagination' => [
                    'total_records' => 17,
                    'total_pages' => 2,
                    'current_page' => 1,
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new TransactionService($client);

        $result = $service->list([
            'customer_id' => 'cust_123',
            'product_id' => 'prod_456',
            'order_id' => 'ord_789',
            'status' => 'paid',
        ], 1, 10);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_contains($request->url(), '/transactions/search')
                && ($data['customer_id'] ?? null) === 'cust_123'
                && ($data['product_id'] ?? null) === 'prod_456'
                && ($data['order_id'] ?? null) === 'ord_789'
                && ($data['status'] ?? null) === 'paid'
                && ($data['page_number'] ?? null) === 1
                && ($data['page_size'] ?? null) === 10;
        });

        $this->assertSame(17, $result['total']);
        $this->assertSame(17, $result['pagination']['total_records']);
    }

    public function test_can_get_transactions_by_customer()
    {
        Http::fake([
            'test-api.creem.io/v1/transactions/search*' => Http::response([
                'items' => [
                    ['id' => 'txn_1', 'customer' => 'cust_123'],
                ],
                'pagination' => [
                    'total_records' => 1,
                    'total_pages' => 1,
                    'current_page' => 1,
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new TransactionService($client);

        $result = $service->byCustomer('cust_123');

        $this->assertArrayHasKey('items', $result);
    }

    public function test_can_get_transactions_by_order()
    {
        Http::fake([
            'test-api.creem.io/v1/transactions/search*' => Http::response([
                'items' => [
                    ['id' => 'txn_1', 'order' => 'ord_456'],
                ],
                'pagination' => [
                    'total_records' => 1,
                    'total_pages' => 1,
                    'current_page' => 1,
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new TransactionService($client);

        $result = $service->byOrder('ord_456');

        $this->assertArrayHasKey('items', $result);
    }

    public function test_can_get_transactions_by_product()
    {
        Http::fake([
            'test-api.creem.io/v1/transactions/search*' => Http::response([
                'items' => [
                    ['id' => 'txn_1', 'product' => 'prod_789'],
                ],
                'pagination' => [
                    'total_records' => 1,
                    'total_pages' => 1,
                    'current_page' => 1,
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new TransactionService($client);

        $result = $service->byProduct('prod_789');

        $this->assertArrayHasKey('items', $result);
    }
}
