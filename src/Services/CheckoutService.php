<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem checkouts.
 */
class CheckoutService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new checkout service instance.
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new checkout session.
     *
     * Returns a checkout URL to redirect customers for payment.
     */
    public function create(array $data): array
    {
        return $this->client->post('/v1/checkouts', $data);
    }
}
