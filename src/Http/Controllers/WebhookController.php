<?php

namespace Romansh\LaravelCreem\Http\Controllers;

use Romansh\LaravelCreem\Events\CheckoutCompleted;
use Romansh\LaravelCreem\Events\PaymentFailed;
use Romansh\LaravelCreem\Events\SubscriptionCanceled;
use Romansh\LaravelCreem\Events\SubscriptionCreated;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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
        $event = $payload['event'] ?? null;

        if (! $event) {
            Log::warning('Creem webhook received without event type', $payload);

            return response()->json(['message' => 'Event type missing'], 400);
        }

        $this->dispatchEvent($event, $payload);

        return response()->json(['message' => 'Webhook processed']);
    }

    /**
     * Dispatch the appropriate event based on webhook type.
     */
    protected function dispatchEvent(string $event, array $payload): void
    {
        match ($event) {
            'checkout.completed' => CheckoutCompleted::dispatch($payload),
            'subscription.created' => SubscriptionCreated::dispatch($payload),
            'subscription.canceled' => SubscriptionCanceled::dispatch($payload),
            'payment.failed' => PaymentFailed::dispatch($payload),
            default => Log::info("Unhandled Creem webhook event: {$event}", $payload),
        };
    }
}
