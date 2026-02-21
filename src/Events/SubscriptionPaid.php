<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired whenever a subscription payment is successfully collected.
 *
 * Corresponds to the "subscription.paid" Creem webhook event.
 * Triggered on every successful billing-cycle renewal, including the
 * very first payment.
 *
 * The {@see $object} array contains the full subscription resource with
 * transaction dates, billing-period boundaries, and merchant metadata.
 *
 * Creem recommends using this event (rather than "subscription.active")
 * to activate or extend a customer's access to your product.
 * See also {@see GrantAccess} which the package dispatches automatically.
 */
class SubscriptionPaid extends CreemEvent
{
    //
}
