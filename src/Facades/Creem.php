<?php

namespace Romansh\LaravelCreem\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for accessing Creem services.
 *
 * @method static \Romansh\LaravelCreem\Services\ProductService products()
 * @method static \Romansh\LaravelCreem\Services\CheckoutService checkouts()
 * @method static \Romansh\LaravelCreem\Services\CustomerService customers()
 * @method static \Romansh\LaravelCreem\Services\SubscriptionService subscriptions()
 * @method static \Romansh\LaravelCreem\Creem profile(string $profile)
 * @method static \Romansh\LaravelCreem\Creem withConfig(array $config)
 *
 * @see \Romansh\LaravelCreem\Creem
 */
class Creem extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'creem';
    }
}
