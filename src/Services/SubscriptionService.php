<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem subscriptions.
 *
 * @see https://docs.creem.io/api-reference/endpoint/get-subscription
 * @see https://docs.creem.io/api-reference/endpoint/update-subscription
 * @see https://docs.creem.io/api-reference/endpoint/upgrade-subscription
 * @see https://docs.creem.io/api-reference/endpoint/pause-subscription
 * @see https://docs.creem.io/api-reference/endpoint/resume-subscription
 * @see https://docs.creem.io/api-reference/endpoint/cancel-subscription
 */
class SubscriptionService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new subscription service instance.
     *
     * @param CreemClient $client
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * List all subscriptions.
     *
     * @see https://docs.creem.io/api-reference/endpoint/list-subscriptions
     *
     * @param int $page
     * @param int $limit
     * @return array<string, mixed>
     */
    public function list(int $page = 1, int $limit = 20): array
    {
        return $this->client->get('/subscriptions', [
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * Retrieve a specific subscription by ID.
     *
     * Note: subscription_id is passed as a query parameter.
     *
     * @see https://docs.creem.io/api-reference/endpoint/get-subscription
     *
     * @param string $subscriptionId
     * @return array<string, mixed>
     */
    public function find(string $subscriptionId): array
    {
        return $this->client->get('/subscriptions', [
            'subscription_id' => $subscriptionId,
        ]);
    }

    /**
     * Cancel a subscription.
     *
     * @see https://docs.creem.io/api-reference/endpoint/cancel-subscription
     *
     * @param string $subscriptionId
     * @param bool $atPeriodEnd Whether to cancel at the end of the billing period
     * @return array<string, mixed>
     */
    public function cancel(string $subscriptionId, bool $atPeriodEnd = true): array
    {
        return $this->client->post("/subscriptions/{$subscriptionId}/cancel", [
            'at_period_end' => $atPeriodEnd,
        ]);
    }

    /**
     * Pause an active subscription.
     *
     * @see https://docs.creem.io/api-reference/endpoint/pause-subscription
     *
     * @param string $subscriptionId
     * @return array<string, mixed>
     */
    public function pause(string $subscriptionId): array
    {
        return $this->client->post("/subscriptions/{$subscriptionId}/pause");
    }

    /**
     * Resume a paused subscription.
     *
     * @see https://docs.creem.io/api-reference/endpoint/resume-subscription
     *
     * @param string $subscriptionId
     * @return array<string, mixed>
     */
    public function resume(string $subscriptionId): array
    {
        return $this->client->post("/subscriptions/{$subscriptionId}/resume");
    }

    /**
     * Upgrade or downgrade a subscription.
     *
     * @see https://docs.creem.io/api-reference/endpoint/upgrade-subscription
     *
     * @param string $subscriptionId
     * @param string $productId
     * @param string $updateBehavior
     * @return array<string, mixed>
     */
    public function upgrade(
        string $subscriptionId,
        string $productId,
        string $updateBehavior = 'proration-charge-immediately'
    ): array {
        return $this->client->post("/subscriptions/{$subscriptionId}/upgrade", [
            'product_id' => $productId,
            'update_behavior' => $updateBehavior,
        ]);
    }

    /**
     * Update subscription data.
     *
     * Using POST as per Creem's specific action-oriented API design.
     *
     * @see https://docs.creem.io/api-reference/endpoint/update-subscription
     *
     * @param string $subscriptionId
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $subscriptionId, array $data): array
    {
        return $this->client->post("/subscriptions/{$subscriptionId}", $data);
    }
}
