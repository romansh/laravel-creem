<?php

namespace Romansh\LaravelCreem;

use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\CheckoutService;
use Romansh\LaravelCreem\Services\CustomerService;
use Romansh\LaravelCreem\Services\ProductService;
use Romansh\LaravelCreem\Services\SubscriptionService;

/**
 * Main entry point for interacting with the Creem API.
 *
 * Usage examples:
 *   Creem::products()->list()
 *   Creem::checkouts()->create([...])
 *   Creem::profile('product_a')->subscriptions()->cancel($id)
 *   Creem::withConfig([...])->checkouts()->create([...])
 */
class Creem
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new Creem instance.
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Use a specific configuration profile.
     */
    public static function profile(string $profile): self
    {
        return new self(CreemClient::fromProfile($profile));
    }

    /**
     * Use inline configuration.
     */
    public static function withConfig(array $config): self
    {
        return new self(CreemClient::fromConfig($config));
    }

    /**
     * Use the default profile.
     */
    public static function make(): self
    {
        return static::profile('default');
    }

    /**
     * Get the product service.
     */
    public function products(): ProductService
    {
        return new ProductService($this->client);
    }

    /**
     * Get the checkout service.
     */
    public function checkouts(): CheckoutService
    {
        return new CheckoutService($this->client);
    }

    /**
     * Get the customer service.
     */
    public function customers(): CustomerService
    {
        return new CustomerService($this->client);
    }

    /**
     * Get the subscription service.
     */
    public function subscriptions(): SubscriptionService
    {
        return new SubscriptionService($this->client);
    }
}
