<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\Exceptions\ApiException;
use Romansh\LaravelCreem\Exceptions\ConfigurationException;
use Romansh\LaravelCreem\Http\CreemClient;

class CreemClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'creem.api_url' => 'https://api.creem.io',
            'creem.test_api_url' => 'https://test-api.creem.io',
            // Set retry to 0 to speed up failure tests and avoid automatic RequestExceptions
            'creem.http.retry.times' => 0,
        ]);
    }

    public function test_it_uses_correct_base_url_based_on_test_mode()
    {
        // Provide a dummy JSON body to prevent TypeError in handleResponse
        Http::fake(['*' => Http::response([])]);

        (new CreemClient('live_key', false))->get('test');
        (new CreemClient('test_key', true))->get('test');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.creem.io/test');
        Http::assertSent(fn ($request) => $request->url() === 'https://test-api.creem.io/test');
    }

    public function test_it_sends_correct_headers_and_payloads()
    {
        Http::fake(['*' => Http::response(['success' => true])]);
        $client = new CreemClient('api_key');

        $client->get('endpoint', ['foo' => 'bar']);
        $client->post('endpoint', ['data' => 'value']);
        $client->delete('endpoint', ['id' => 1]);

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'api_key') &&
                   $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_it_throws_api_exception_on_failure()
    {
        Http::fake([
            '*' => Http::response([
                'error' => 'Not Found',
                'message' => ['The requested resource was not found.'], // Must be array
                'trace_id' => '12345'
            ], 404)
        ]);

        $client = new CreemClient('api_key');

        $this->expectException(ApiException::class);

        try {
            $client->get('invalid-route');
        } catch (ApiException $e) {
            $this->assertEquals(404, $e->getStatusCode());
            $this->assertEquals('12345', $e->getTraceId()); // Testing that setter line
            throw $e;
        }
    }

    public function test_from_profile_factory_throws_exception_if_profile_missing()
    {
        config(['creem.profiles.exists' => null]);

        $this->expectException(ConfigurationException::class);
        CreemClient::fromProfile('non-existent');
    }

    public function test_from_profile_factory_creates_instance()
    {
        config(['creem.profiles.default' => [
            'api_key' => 'key_123',
            'test_mode' => true
        ]]);

        $client = CreemClient::fromProfile('default');
        $this->assertInstanceOf(CreemClient::class, $client);
    }

    public function test_from_config_throws_exception_if_key_is_missing()
    {
        $this->expectException(ConfigurationException::class);
        CreemClient::fromConfig(['test_mode' => true]);
    }

    public function test_from_config_throws_exception_if_key_is_empty()
    {
        $this->expectException(ConfigurationException::class);
        CreemClient::fromConfig(['api_key' => '']);
    }

    public function test_from_config_creates_instance_with_defaults()
    {
        Http::fake(['*' => Http::response([])]);

        $client = CreemClient::fromConfig(['api_key' => 'valid_key']);
        $this->assertInstanceOf(CreemClient::class, $client);

        $client->get('ping');
        Http::assertSent(fn ($request) => $request->url() === 'https://api.creem.io/ping');
    }
}
