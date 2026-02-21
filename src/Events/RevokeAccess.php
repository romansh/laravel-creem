<?php

namespace Romansh\LaravelCreem\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Application-level event dispatched when a customer's access should be revoked.
 *
 * This is NOT a direct Creem webhook event. The package dispatches it
 * internally in response to "subscription.canceled", providing a single,
 * stable hook for access-revocation logic regardless of how the cancellation
 * was initiated (by the merchant, the customer, or automatic payment failure).
 *
 * The {@see $customer} array is the full Creem customer object.
 * The {@see $metadata} array contains the merchant-defined metadata from
 * the originating subscription. Use the metadata fields (e.g. "referenceId")
 * to look up the corresponding internal user.
 *
 * Example listener:
 *
 *   public function handle(RevokeAccess $event): void
 *   {
 *       $userId = $event->metadata['referenceId'] ?? null;
 *       // revoke access for $userId / $event->customer['email']
 *   }
 */
class RevokeAccess
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
     * Merchant-defined metadata from the originating subscription.
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
     * @param array<string, mixed> $metadata Metadata from the originating subscription.
     * @param array<string, mixed> $payload  Full raw payload of the originating webhook.
     */
    public function __construct(array $customer, array $metadata, array $payload)
    {
        $this->customer = $customer;
        $this->metadata = $metadata;
        $this->payload  = $payload;
    }
}
