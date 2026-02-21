<?php

namespace Romansh\LaravelCreem\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Application-level event dispatched when a customer should be granted access.
 *
 * This is NOT a direct Creem webhook event. The package dispatches it
 * internally in response to "checkout.completed" or "subscription.paid",
 * providing a single, stable hook for access-provisioning logic regardless
 * of which underlying Creem event triggered it.
 *
 * The {@see $customer} array is the full Creem customer object.
 * The {@see $metadata} array contains the merchant-defined metadata from
 * the originating resource (checkout or subscription). Use the metadata
 * fields (e.g. "referenceId") to look up the corresponding internal user.
 *
 * Example listener:
 *
 *   public function handle(GrantAccess $event): void
 *   {
 *       $userId = $event->metadata['referenceId'] ?? null;
 *       // provision access for $userId / $event->customer['email']
 *   }
 */
class GrantAccess
{
    use Dispatchable;
    use SerializesModels;

    /**
     * The Creem customer object from the originating event.
     *
     * @var array<string, mixed>
     */
    public readonly array $customer;

    /**
     * Merchant-defined metadata from the originating resource.
     * May contain fields such as "referenceId" to identify the internal user.
     *
     * @var array<string, mixed>
     */
    public readonly array $metadata;

    /**
     * The raw payload of the originating Creem webhook event.
     *
     * @var array<string, mixed>
     */
    public readonly array $payload;

    /**
     * @param array<string, mixed> $customer Creem customer object.
     * @param array<string, mixed> $metadata Metadata from the originating resource.
     * @param array<string, mixed> $payload  Full raw payload of the originating webhook.
     */
    public function __construct(array $customer, array $metadata, array $payload)
    {
        $this->customer = $customer;
        $this->metadata = $metadata;
        $this->payload  = $payload;
    }
}
