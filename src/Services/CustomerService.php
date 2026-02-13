<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem customers.
 */
class CustomerService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new customer service instance.
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieve a customer by ID.
     */
    public function find(string $customerId): array
    {
        return $this->client->get('/v1/customers', [
            'customer_id' => $customerId,
        ]);
    }

    /**
     * Retrieve a customer by email address.
     */
    public function findByEmail(string $email): array
    {
        return $this->client->get('/v1/customers', [
            'email' => $email,
        ]);
    }

    /**
     * List all customers with pagination.
     */
    public function list(int $page = 1, int $pageSize = 20): array
    {
        return $this->client->get('/v1/customers/list', [
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

    /**
     * Generate a customer portal link for managing billing and subscriptions.
     */
    public function createPortalLink(string $customerId): string
    {
        $response = $this->client->post('/v1/customers/billing', [
            'customer_id' => $customerId,
        ]);

        return $response['customer_portal_link'];
    }
}
