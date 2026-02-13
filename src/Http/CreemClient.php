<?php

namespace Romansh\LaravelCreem\Http;

use Romansh\LaravelCreem\Exceptions\ApiException;
use Romansh\LaravelCreem\Exceptions\ConfigurationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * HTTP client wrapper for Creem API requests.
 */
class CreemClient
{
    /**
     * The API key for authentication.
     */
    protected string $apiKey;

    /**
     * Whether test mode is enabled.
     */
    protected bool $testMode;

    /**
     * Create a new Creem client instance.
     */
    public function __construct(string $apiKey, bool $testMode = false)
    {
        $this->apiKey = $apiKey;
        $this->testMode = $testMode;
    }

    /**
     * Get the base URL for the API.
     */
    protected function baseUrl(): string
    {
        return $this->testMode
            ? 'https://test-api.creem.io'
            : 'https://api.creem.io';
    }

    /**
     * Create a configured HTTP client instance.
     */
    protected function client(): PendingRequest
    {
        $timeout = config('creem.http.timeout', 30);
        $retryTimes = config('creem.http.retry.times', 3);
        $retrySleep = config('creem.http.retry.sleep', 100);

        return Http::baseUrl($this->baseUrl())
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout($timeout)
            ->retry($retryTimes, $retrySleep);
    }

    /**
     * Send a GET request.
     */
    public function get(string $uri, array $query = []): array
    {
        $response = $this->client()->get($uri, $query);

        return $this->handleResponse($response);
    }

    /**
     * Send a POST request.
     */
    public function post(string $uri, array $data = []): array
    {
        $response = $this->client()->post($uri, $data);

        return $this->handleResponse($response);
    }

    /**
     * Handle the API response.
     *
     * @throws ApiException
     */
    protected function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $data = $response->json();

        throw ApiException::fromResponse($data, $response->status());
    }

    /**
     * Create a client instance from a profile name.
     *
     * @throws ConfigurationException
     */
    public static function fromProfile(string $profile = 'default'): self
    {
        $config = config("creem.profiles.{$profile}");

        if (! $config) {
            throw ConfigurationException::profileNotFound($profile);
        }

        return static::fromConfig($config);
    }

    /**
     * Create a client instance from an array of configuration.
     *
     * @throws ConfigurationException
     */
    public static function fromConfig(array $config): self
    {
        $required = ['api_key'];
        $missing = array_diff($required, array_keys($config));

        if (count($missing) > 0) {
            throw ConfigurationException::invalidInlineConfig($missing);
        }

        if (empty($config['api_key'])) {
            throw ConfigurationException::missingApiKey();
        }

        return new self(
            $config['api_key'],
            $config['test_mode'] ?? false
        );
    }
}
