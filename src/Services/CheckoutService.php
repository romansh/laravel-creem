<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem checkouts.
 *
 * @see https://docs.creem.io/api-reference/endpoint/create-checkout
 * @see https://docs.creem.io/api-reference/endpoint/get-checkout
 */
class CheckoutService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new checkout service instance.
     *
     * @param CreemClient $client
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new checkout session to accept one-time payments or start subscriptions.
     *
     * Returns a checkout URL to redirect customers for payment.
     *
     * @see https://docs.creem.io/api-reference/endpoint/create-checkout
     *
     * @param array<string, mixed> $data Checkout creation payload
     * @return array<string, mixed> Returns checkout session with checkout_url
     */
    public function create(array $data): array
    {
        return $this->client->post('/checkouts', $data);
    }

    /**
     * Retrieve details of a checkout session by ID.
     *
     * View status, customer info, and payment details.
     *
     * Note: checkout_id is passed as a query parameter.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-checkout
     *
     * @param string $checkoutId The ID of the checkout session to retrieve
     * @return array<string, mixed>
     */
    public function find(string $checkoutId): array
    {
        return $this->client->get('/checkouts', [
            'checkout_id' => $checkoutId,
        ]);
    }
}