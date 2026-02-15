<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem discount codes.
 *
 * @see https://docs.creem.io/api-reference/endpoint/create-discount-code
 * @see https://docs.creem.io/api-reference/endpoint/get-discount-code
 * @see https://docs.creem.io/api-reference/endpoint/delete-discount-code
 */
class DiscountService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new discount service instance.
     *
     * @param CreemClient $client
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new discount code for one-time or recurring discounts.
     *
     * Set percentage or fixed amount discounts with expiration dates and redemption limits.
     *
     * @see https://docs.creem.io/api-reference/endpoint/create-discount-code
     *
     * @param array<string, mixed> $data Discount creation payload
     * @return array<string, mixed> Returns created discount with code and settings
     */
    public function create(array $data): array
    {
        return $this->client->post('/discounts', $data);
    }

    /**
     * Retrieve discount code details by ID.
     *
     * Check usage limits, expiration, and discount amount.
     *
     * Note: discount_id is passed as a query parameter.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-discount-code
     *
     * @param string $discountId The unique identifier of the discount
     * @return array<string, mixed>
     */
    public function find(string $discountId): array
    {
        return $this->client->get('/discounts', [
            'discount_id' => $discountId,
        ]);
    }

    /**
     * Retrieve discount code details by code.
     *
     * Check usage limits, expiration, and discount amount.
     *
     * Note: discount_code is passed as a query parameter.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-discount-code
     *
     * @param string $code The discount code to retrieve
     * @return array<string, mixed>
     */
    public function findByCode(string $code): array
    {
        return $this->client->get('/discounts', [
            'discount_code' => $code,
        ]);
    }

    /**
     * Permanently delete a discount code.
     *
     * Prevent further usage of the discount. This action cannot be undone.
     *
     * @see https://docs.creem.io/api-reference/endpoint/delete-discount-code
     *
     * @param string $discountId The unique identifier of the discount to delete
     * @return array<string, mixed>
     */
    public function delete(string $discountId): array
    {
        return $this->client->delete("/discounts/{$discountId}/delete");
    }
}
