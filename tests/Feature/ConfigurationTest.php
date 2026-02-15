<?php

namespace Romansh\LaravelCreem\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\Creem;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Exceptions\ConfigurationException;

class ConfigurationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CreemServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'creem.profiles.default' => [
                'api_key' => 'default_key',
                'test_mode' => false,
            ],
            'creem.profiles.product_a' => [
                'api_key' => 'product_a_key',
                'test_mode' => true,
            ],
        ]);
    }

    public function test_can_use_default_profile()
    {
        $creem = Creem::profile('default');

        $this->assertInstanceOf(Creem::class, $creem);
    }

    public function test_can_use_named_profile()
    {
        $creem = Creem::profile('product_a');

        $this->assertInstanceOf(Creem::class, $creem);
    }

    public function test_throws_exception_for_missing_profile()
    {
        $this->expectException(ConfigurationException::class);

        Creem::profile('nonexistent');
    }

    public function test_can_use_inline_config()
    {
        $creem = Creem::withConfig([
            'api_key' => 'inline_key',
            'test_mode' => true,
        ]);

        $this->assertInstanceOf(Creem::class, $creem);
    }

    public function test_throws_exception_for_invalid_inline_config()
    {
        $this->expectException(ConfigurationException::class);

        Creem::withConfig([
            'test_mode' => true,
        ]);
    }

    public function test_throws_exception_for_empty_api_key()
    {
        $this->expectException(ConfigurationException::class);

        Creem::withConfig([
            'api_key' => '',
            'test_mode' => true,
        ]);
    }
}
