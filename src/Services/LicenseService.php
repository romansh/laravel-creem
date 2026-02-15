<?php

namespace Romansh\LaravelCreem\Services;

use Romansh\LaravelCreem\Http\CreemClient;

/**
 * Service for managing Creem license keys.
 *
 * @see https://docs.creem.io/api-reference/endpoint/validate-license
 * @see https://docs.creem.io/api-reference/endpoint/activate-license
 * @see https://docs.creem.io/api-reference/endpoint/deactivate-license
 */
class LicenseService
{
    /**
     * The HTTP client instance.
     */
    protected CreemClient $client;

    /**
     * Create a new license service instance.
     *
     * @param CreemClient $client
     */
    public function __construct(CreemClient $client)
    {
        $this->client = $client;
    }

    /**
     * Verify if a license key is valid and active for a specific instance.
     *
     * Check activation status and expiration.
     *
     * @see https://docs.creem.io/api-reference/endpoint/validate-license
     *
     * @param string $key The license key to validate
     * @param string $instanceId ID of the instance to validate
     * @return array<string, mixed> Returns license status and details
     */
    public function validate(string $key, string $instanceId): array
    {
        return $this->client->post('/licenses/validate', [
            'key' => $key,
            'instance_id' => $instanceId,
        ]);
    }

    /**
     * Activate a license key for a specific device or instance.
     *
     * Register new activations and track usage limits.
     *
     * @see https://docs.creem.io/api-reference/endpoint/activate-license
     *
     * @param string $key The license key to activate
     * @param string $instanceName A label for the new instance to identify it in Creem
     * @return array<string, mixed> Returns activated license with instance details
     */
    public function activate(string $key, string $instanceName): array
    {
        return $this->client->post('/licenses/activate', [
            'key' => $key,
            'instance_name' => $instanceName,
        ]);
    }

    /**
     * Remove a device activation from a license key.
     *
     * Deactivate a specific instance to free up activation slots or revoke access.
     *
     * @see https://docs.creem.io/api-reference/endpoint/deactivate-license
     *
     * @param string $key The license key to deactivate
     * @param string $instanceId ID of the instance to deactivate
     * @return array<string, mixed> Returns updated license information
     */
    public function deactivate(string $key, string $instanceId): array
    {
        return $this->client->post('/licenses/deactivate', [
            'key' => $key,
            'instance_id' => $instanceId,
        ]);
    }
}
