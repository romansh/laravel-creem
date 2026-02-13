<?php

namespace App\Http\Controllers;

use Romansh\LaravelCreem\Events\CheckoutCompleted;
use Romansh\LaravelCreem\Events\SubscriptionCanceled;
use Illuminate\Http\Request;

/**
 * Example custom webhook handler using Laravel event listeners.
 *
 * Register these listeners in EventServiceProvider:
 *
 * protected $listen = [
 *     CheckoutCompleted::class => [
 *         SendPurchaseConfirmation::class,
 *         ProvisionAccess::class,
 *     ],
 *     SubscriptionCanceled::class => [
 *         RevokeAccess::class,
 *         SendCancellationEmail::class,
 *     ],
 * ];
 */
class WebhookExampleController extends Controller
{
    /**
     * This is just for demonstration.
     * In practice, you would use Laravel's event listeners instead.
     */
    public function handleCheckoutCompleted(CheckoutCompleted $event)
    {
        $checkout = $event->payload['data'] ?? [];

        // Send confirmation email
        // Mail::to($checkout['customer']['email'])
        //     ->send(new PurchaseConfirmation($checkout));

        // Provision access
        // User::where('email', $checkout['customer']['email'])
        //     ->first()
        //     ->grantAccess($checkout['product_id']);
    }
}
