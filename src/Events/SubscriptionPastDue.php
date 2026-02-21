<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a subscription payment fails and the subscription becomes past due.
 *
 * Corresponds to the "subscription.past_due" Creem webhook event.
 * This occurs when a payment attempt fails (e.g. card declined, insufficient funds).
 *
 * The {@see $object} array contains the full subscription resource with
 * status "past_due" and the last known transaction details.
 *
 * Creem will automatically retry the payment on a progressive schedule.
 * If a retry succeeds the subscription returns to "active" and a
 * {@see SubscriptionPaid} event is dispatched. If all retries are
 * exhausted the subscription transitions to "canceled".
 *
 * Use this event to warn the customer to update their payment method.
 */
class SubscriptionPastDue extends CreemEvent
{
    //
}
