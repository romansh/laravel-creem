<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\Creem;
use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\CheckoutService;
use Romansh\LaravelCreem\Services\CustomerService;
use Romansh\LaravelCreem\Services\DiscountService;
use Romansh\LaravelCreem\Services\LicenseService;
use Romansh\LaravelCreem\Services\ProductService;
use Romansh\LaravelCreem\Services\SubscriptionService;
use Romansh\LaravelCreem\Services\TransactionService;

class CreemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Setup default config so Creem::make() can work
        config([
            'creem.profiles.default' => [
                'api_key' => 'test_key',
            ],
            'creem.profiles.custom' => [
                'api_key' => 'custom_key',
            ]
        ]);
    }

    /**
     * Test the static factory methods.
     */
    public function test_static_factories()
    {
        // 1. Test make() - this covers profile('default')
        $creemDefault = Creem::make();
        $this->assertInstanceOf(Creem::class, $creemDefault);

        // 2. Test profile() with a specific name
        $creemCustom = Creem::profile('custom');
        $this->assertInstanceOf(Creem::class, $creemCustom);

        // 3. Test withConfig() with inline array
        $creemInline = Creem::withConfig(['api_key' => 'inline_key']);
        $this->assertInstanceOf(Creem::class, $creemInline);
    }

    /**
     * Test that services are correctly instantiated.
     */
    public function test_services_return_correct_instances()
    {
        $client = new CreemClient('api_key');
        $creem = new Creem($client);

        $this->assertInstanceOf(ProductService::class, $creem->products());
        $this->assertInstanceOf(CheckoutService::class, $creem->checkouts());
        $this->assertInstanceOf(CustomerService::class, $creem->customers());
        $this->assertInstanceOf(SubscriptionService::class, $creem->subscriptions());
        $this->assertInstanceOf(DiscountService::class, $creem->discounts());
        $this->assertInstanceOf(LicenseService::class, $creem->licenses());
        $this->assertInstanceOf(TransactionService::class, $creem->transactions());
    }
}
