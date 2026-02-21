<?php

namespace Romansh\LaravelCreem\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Abstract base for every Creem webhook event.
 *
 * Subclasses receive the full decoded JSON payload and expose only
 * top-level envelope fields as typed properties. The raw "object" block
 * is available via {@see $object} for any deeper field access.
 */
abstract class CreemEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Unique event identifier supplied by Creem (e.g. "evt_5WHHcZPv7VS0YUsberIuOz").
     */
    public readonly string $eventId;

    /**
     * Event type string as sent by Creem (e.g. "checkout.completed").
     */
    public readonly string $eventType;

    /**
     * Unix timestamp in milliseconds when Creem emitted the event.
     */
    public readonly int $createdAt;

    /**
     * The "object" block from the webhook payload.
     * Contains the full resource data relevant to the event.
     *
     * @var array<string, mixed>
     */
    public readonly array $object;

    /**
     * The complete raw webhook payload for unrestricted access.
     *
     * @var array<string, mixed>
     */
    public readonly array $payload;

    /**
     * @param array<string, mixed> $payload Fully decoded webhook JSON body.
     */
    public function __construct(array $payload)
    {
        $this->payload   = $payload;
        $this->eventId   = $payload['id'];
        $this->eventType = $payload['eventType'];
        $this->createdAt = $payload['created_at'];
        $this->object    = $payload['object'];
    }
}
