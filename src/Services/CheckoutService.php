<?php

namespace Romansh\LaravelCreem\Services;

use InvalidArgumentException;
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
        $this->validateCreatePayload($data);

        return $this->client->post('/checkouts', $data);
    }

    /**
     * Guard against ambiguous pricing payloads for saved-card style charges.
     *
     * Checkout-hosted flows can resolve regional prices from a product, but
     * direct/off-session flows should always specify the exact price.
     *
     * @param array<string, mixed> $data
     */
    private function validateCreatePayload(array $data): void
    {
        $hasProductId = isset($data['product_id']) && $data['product_id'] !== '';
        $hasPriceId = isset($data['price_id']) && $data['price_id'] !== '';
        $isDirectCharge = isset($data['payment_method_id']) || (($data['off_session'] ?? false) === true);

        if ($hasProductId && $hasPriceId) {
            throw new InvalidArgumentException('Provide either product_id or price_id for checkout creation, not both.');
        }

        if ($isDirectCharge && $hasProductId && ! $hasPriceId) {
            throw new InvalidArgumentException('Direct or off-session checkout creation must use an explicit price_id instead of product_id to avoid regional pricing mismatches.');
        }
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
