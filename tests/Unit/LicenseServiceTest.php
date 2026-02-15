<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\CreemServiceProvider;
use Romansh\LaravelCreem\Http\CreemClient;
use Romansh\LaravelCreem\Services\LicenseService;

class LicenseServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['creem.profiles.default' => [
            'api_key' => 'test_api_key',
            'test_mode' => true,
            'webhook_secret' => 'test_webhook_secret',
        ]]);
    }

    protected function getPackageProviders($app)
    {
        return [
            CreemServiceProvider::class,
        ];
    }

    public function test_can_validate_license()
    {
        Http::fake([
            'test-api.creem.io/v1/licenses/validate' => Http::response([
                'id' => 'lic_123',
                'key' => 'ABC123-XYZ456-XYZ456-XYZ456',
                'status' => 'active',
                'activation' => 1,
                'activation_limit' => 5,
                'expires_at' => '2025-12-31T23:59:59Z',
                'instance' => [
                    'id' => 'inst_123',
                    'status' => 'active',
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new LicenseService($client);

        $result = $service->validate('ABC123-XYZ456-XYZ456-XYZ456', 'inst_123');

        $this->assertEquals('active', $result['status']);
        $this->assertEquals('inst_123', $result['instance']['id']);
    }

    public function test_can_activate_license()
    {
        Http::fake([
            'test-api.creem.io/v1/licenses/activate' => Http::response([
                'id' => 'lic_123',
                'key' => 'ABC123-XYZ456-XYZ456-XYZ456',
                'status' => 'active',
                'activation' => 1,
                'instance' => [
                    'id' => 'inst_new',
                    'name' => 'johns-macbook-pro',
                    'status' => 'active',
                ],
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new LicenseService($client);

        $result = $service->activate('ABC123-XYZ456-XYZ456-XYZ456', 'johns-macbook-pro');

        $this->assertEquals('active', $result['status']);
        $this->assertEquals('johns-macbook-pro', $result['instance']['name']);
        $this->assertEquals(1, $result['activation']);
    }

    public function test_can_deactivate_license()
    {
        Http::fake([
            'test-api.creem.io/v1/licenses/deactivate' => Http::response([
                'id' => 'lic_123',
                'key' => 'ABC123-XYZ456-XYZ456-XYZ456',
                'status' => 'active',
                'activation' => 0,
            ], 200),
        ]);

        $client = CreemClient::fromProfile('default');
        $service = new LicenseService($client);

        $result = $service->deactivate('ABC123-XYZ456-XYZ456-XYZ456', 'inst_123');

        $this->assertEquals('lic_123', $result['id']);
        $this->assertEquals(0, $result['activation']);
    }
}
