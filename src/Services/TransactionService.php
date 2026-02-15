<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem transactions.
 *
 * @see https://docs.creem.io/api-reference/endpoint/get-transaction
 * @see https://docs.creem.io/api-reference/endpoint/get-transactions
 */
class TransactionService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new transaction service instance.
     *
     * @param CreemClient $client
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieve a single transaction by ID.
     *
     * View payment details, status, and associated order information.
     *
     * Note: transaction_id is passed as a query parameter.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-transaction
     *
     * @param string $transactionId The unique identifier of the transaction
     * @return array<string, mixed>
     */
    public function find(string $transactionId): array
    {
        return $this->client->get('/transactions', [
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * Search and retrieve payment transactions.
     *
     * Filter by customer, product, date range, and status.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-transactions
     *
     * @param array<string, mixed> $filters Optional filters (customer_id, order_id, product_id)
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @return array<string, mixed> Returns items and pagination data
     */
    public function list(array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        return $this->client->get('/transactions/search', array_merge($filters, [
            'page_number' => $page,
            'page_size' => $pageSize,
        ]));
    }

    /**
     * Alias for the list method.
     *
     * @param array<string, mixed> $filters Optional filters (customer_id, order_id, product_id)
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @return array<string, mixed> Returns items and pagination data
     */
    public function all(array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        return $this->list($filters, $page, $pageSize);
    }

    /**
     * Get transactions for a specific customer.
     *
     * @param string $customerId The customer ID to filter by
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @return array<string, mixed> Returns items and pagination data
     */
    public function byCustomer(string $customerId, int $page = 1, int $pageSize = 20): array
    {
        return $this->list(['customer_id' => $customerId], $page, $pageSize);
    }

    /**
     * Get transactions for a specific order.
     *
     * @param string $orderId The order ID to filter by
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @return array<string, mixed> Returns items and pagination data
     */
    public function byOrder(string $orderId, int $page = 1, int $pageSize = 20): array
    {
        return $this->list(['order_id' => $orderId], $page, $pageSize);
    }

    /**
     * Get transactions for a specific product.
     *
     * @param string $productId The product ID to filter by
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @return array<string, mixed> Returns items and pagination data
     */
    public function byProduct(string $productId, int $page = 1, int $pageSize = 20): array
    {
        return $this->list(['product_id' => $productId], $page, $pageSize);
    }
}
