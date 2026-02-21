<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a subscription expires because the billing period ended without a new payment.
 *
 * Corresponds to the "subscription.expired" Creem webhook event.
 * Payment retries may still happen at this stage. The subscription is
 * only terminal when its status transitions to "canceled".
 *
 * The {@see $object} array contains the full subscription resource with
 * the billing-period boundaries and the last known transaction details.
 *
 * Use this event to flag the account for potential access restriction
 * while awaiting the final retry outcome.
 */
class SubscriptionExpired extends CreemEvent
{
    //
}
