<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem customers.
 *
 * @see https://docs.creem.io/api-reference/endpoint/get-customer
 * @see https://docs.creem.io/api-reference/endpoint/list-customers
 * @see https://docs.creem.io/api-reference/endpoint/create-customer-billing
 */
class CustomerService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new customer service instance.
     *
     * @param CreemClient $client
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieve a customer by ID.
     *
     * View purchase history, subscriptions, and profile details.
     *
     * Note: customer_id is passed as a query parameter.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-customer
     *
     * @param string $customerId The unique identifier of the customer
     * @return array<string, mixed>
     */
    public function find(string $customerId): array
    {
        return $this->client->get('/customers', [
            'customer_id' => $customerId,
        ]);
    }

    /**
     * Retrieve a customer by email address.
     *
     * View purchase history, subscriptions, and profile details.
     *
     * Note: email is passed as a query parameter.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-customer
     *
     * @param string $email The unique email of the customer
     * @return array<string, mixed>
     */
    public function findByEmail(string $email): array
    {
        return $this->client->get('/customers', [
            'email' => $email,
        ]);
    }

    /**
     * Retrieve a paginated list of all customers.
     *
     * Filter and search through your customer base.
     *
     * @see https://docs.creem.io/api-reference/endpoint/list-customers
     *
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @return array<string, mixed> Returns items and pagination data
     */
    public function list(int $page = 1, int $pageSize = 20): array
    {
        return $this->client->get('/customers/list', [
            'page_number' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * Alias for the list method.
     *
     * @param int $page The page number (default: 1)
     * @param int $pageSize The page size (default: 20)
     * @return array<string, mixed> Returns items and pagination data
     */
    public function all(int $page = 1, int $pageSize = 20): array
    {
        return $this->list($page, $pageSize);
    }

    /**
     * Generate a customer portal link for managing billing and subscriptions.
     *
     * Allows customers to manage their subscriptions, payment methods, and personal information.
     *
     * @see https://docs.creem.io/api-reference/endpoint/create-customer-billing
     *
     * @param string $customerId The unique identifier of the customer
     * @return string The customer portal URL
     */
    public function createPortalLink(string $customerId): string
    {
        $response = $this->client->post('/customers/billing', [
            'customer_id' => $customerId,
        ]);

        return $response['customer_portal_link'];
    }
}