<?php

namespace Romansh\LaravelCreem\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Romansh\LaravelCreem\Events\CheckoutCompleted;
use Romansh\LaravelCreem\Events\DisputeCreated;
use Romansh\LaravelCreem\Events\GrantAccess;
use Romansh\LaravelCreem\Events\PaymentFailed;
use Romansh\LaravelCreem\Events\RefundCreated;
use Romansh\LaravelCreem\Events\RevokeAccess;
use Romansh\LaravelCreem\Events\SubscriptionActive;
use Romansh\LaravelCreem\Events\SubscriptionCanceled;
use Romansh\LaravelCreem\Events\SubscriptionCreated;
use Romansh\LaravelCreem\Events\SubscriptionExpired;
use Romansh\LaravelCreem\Events\SubscriptionPaid;
use Romansh\LaravelCreem\Events\SubscriptionPastDue;
use Romansh\LaravelCreem\Events\SubscriptionPaused;
use Romansh\LaravelCreem\Events\SubscriptionScheduledCancel;
use Romansh\LaravelCreem\Events\SubscriptionTrialing;
use Romansh\LaravelCreem\Events\SubscriptionUpdate;

/**
 * Controller for handling Creem webhook events.
 */
class WebhookController extends Controller
{
    /**
     * Handle incoming webhook requests.
     */
    public function __invoke(Request $request)
    {
        $payload = $request->all();
        $event = $payload['eventType'] ?? $payload['event'] ?? null;

        if (! $event) {
            Log::warning('Creem webhook received without event type', $payload);

            return response()->json(['message' => 'Event type missing'], 400);
        }

        // Normalize payload to the CreemEvent shape expected by event classes.
        $data = $payload['data'] ?? [];

        $normalized = [
            'id' => $data['id'] ?? $payload['id'] ?? 'evt_unknown',
            'eventType' => $event,
            'created_at' => $payload['created_at'] ?? (int) (microtime(true) * 1000),
            'object' => $data,
        ];

        $this->dispatchEvent($event, $normalized, $payload);

        return response()->json(['message' => 'Webhook processed']);
    }

    /**
     * Dispatch the appropriate event based on webhook type.
     */
    protected function dispatchEvent(string $event, array $payload, ?array $rawPayload = null): void
    {
        $map = [
            'checkout.completed' => CheckoutCompleted::class,
            'dispute.created' => DisputeCreated::class,
            'refund.created' => RefundCreated::class,

            // Subscription lifecycle
            'subscription.created' => SubscriptionCreated::class,
            'subscription.active' => SubscriptionActive::class,
            'subscription.paid' => SubscriptionPaid::class,
            'subscription.canceled' => SubscriptionCanceled::class,
            'subscription.expired' => SubscriptionExpired::class,
            'subscription.past_due' => SubscriptionPastDue::class,
            'subscription.paused' => SubscriptionPaused::class,
            'subscription.trialing' => SubscriptionTrialing::class,
            'subscription.scheduled_cancel' => SubscriptionScheduledCancel::class,
            'subscription.updated' => SubscriptionUpdate::class,
            'subscription.update' => SubscriptionUpdate::class,

            // Payments
            'payment.failed' => PaymentFailed::class,
        ];

        if (isset($map[$event])) {
            $class = $map[$event];
            $class::dispatch($payload);
            // Dispatch application-level access events when appropriate.
            // Extract customer and metadata from the normalized payload object.
            $object = $payload['object'] ?? [];
            $customer = $object['customer'] ?? [];
            $metadata = $object['metadata'] ?? [];

            // Grant access after a successful checkout or subscription payment.
            if (in_array($event, ['checkout.completed', 'subscription.paid'], true)) {
                GrantAccess::dispatch($customer, $metadata, $rawPayload ?? $payload);
            }

            // Revoke access on cancellation or expiration.
            if (in_array($event, ['subscription.canceled', 'subscription.expired'], true)) {
                RevokeAccess::dispatch($customer, $metadata, $rawPayload ?? $payload);
            }
            return;
        }

        // Log the original raw payload for unhandled events when available
        $toLog = $rawPayload ?? $payload;
        Log::info("Unhandled Creem webhook event: {$event}", $toLog);
    }
}
