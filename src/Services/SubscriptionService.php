<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem subscriptions.
 */
class SubscriptionService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new subscription service instance.
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieve a subscription by ID.
     */
    public function find(string $subscriptionId): array
    {
        return $this->client->get('/v1/subscriptions', [
            'subscription_id' => $subscriptionId,
        ]);
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(string $subscriptionId): array
    {
        return $this->client->post("/v1/subscriptions/{$subscriptionId}/cancel");
    }

    /**
     * Pause a subscription.
     */
    public function pause(string $subscriptionId): array
    {
        return $this->client->post("/v1/subscriptions/{$subscriptionId}/pause");
    }

    /**
     * Resume a paused subscription.
     */
    public function resume(string $subscriptionId): array
    {
        return $this->client->post("/v1/subscriptions/{$subscriptionId}/resume");
    }

    /**
     * Upgrade a subscription to a different product.
     */
    public function upgrade(string $subscriptionId, string $productId, string $updateBehavior = 'proration-charge-immediately'): array
    {
        return $this->client->post("/v1/subscriptions/{$subscriptionId}/upgrade", [
            'product_id' => $productId,
            'update_behavior' => $updateBehavior,
        ]);
    }
}
