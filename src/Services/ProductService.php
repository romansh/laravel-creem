<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\Concerns\NormalizesPaginatedResponses;

/**
 * Service for managing Creem products.
 *
 * @see https://docs.creem.io/api-reference/endpoint/create-product
 * @see https://docs.creem.io/api-reference/endpoint/get-product
 * @see https://docs.creem.io/api-reference/endpoint/search-products
 */
class ProductService
{
    use NormalizesPaginatedResponses;

    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new product service instance.
     *
     * @param CreemClient $client
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new product for one-time payments or subscriptions.
     *
     * Configure pricing, billing cycles, and features.
     *
     * @see https://docs.creem.io/api-reference/endpoint/create-product
     *
     * @param array<string, mixed> $data Product creation payload
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->client->post('/products', $data);
    }

    /**
     * Retrieve a specific product by ID.
     *
     * View pricing, billing type, status, and product configuration.
     *
     * Note: product_id is passed as a query parameter.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-product
     *
     * @param string $productId The unique identifier of the product
     * @return array<string, mixed>
     */
    public function find(string $productId): array
    {
        return $this->client->get('/products', [
            'product_id' => $productId,
        ]);
    }

    /**
     * Search and retrieve a paginated list of products.
     *
     * Filter by status, billing type, and other criteria.
     *
     * @see https://docs.creem.io/api-reference/endpoint/search-products
     *
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @param array<string, mixed> $filters Optional filters supported by Creem
     * @return array<string, mixed> Returns items and pagination data
     */
    public function list(int $page = 1, int $pageSize = 20, array $filters = []): array
    {
        return $this->normalizePaginatedResponse($this->client->get('/products/search', array_merge(
            array_filter($filters, static fn ($value) => $value !== null && $value !== ''),
            [
                'page_number' => $page,
                'page_size' => $pageSize,
            ]
        )));
    }

    /**
     * Alias for the list method.
     *
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @param array<string, mixed> $filters Optional filters supported by Creem
     * @return array<string, mixed> Returns items and pagination data
     */
    public function all(int $page = 1, int $pageSize = 20, array $filters = []): array
    {
        return $this->list($page, $pageSize, $filters);
    }
}
