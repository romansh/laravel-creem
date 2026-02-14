<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem products.
 */
class ProductService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new product service instance.
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new product.
     */
    public function create(array $data): array
    {
        return $this->client->post('/products', $data);
    }

    /**
     * Retrieve a product by ID.
     */
    public function find(string $productId): array
    {
        return $this->client->get('/products', [
            'product_id' => $productId,
        ]);
    }

    /**
     * List all products with pagination.
     */
    public function list(int $page = 1, int $pageSize = 20): array
    {
        return $this->client->get('/products/search', [
            'page_number' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * Alias for list method.
     */
    public function all(int $page = 1, int $pageSize = 20): array
    {
        return $this->list($page, $pageSize);
    }
}
