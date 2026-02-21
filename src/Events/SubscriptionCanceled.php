<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a subscription is canceled immediately by the merchant or customer.
 *
 * Corresponds to the "subscription.canceled" Creem webhook event.
 * The {@see $object} array contains the full subscription resource with
 * the final transaction details, billing-period boundaries, and the
 * exact cancellation timestamp in the "canceled_at" field.
 *
 * Use this event to revoke access immediately upon cancellation.
 * See also {@see RevokeAccess} which the package dispatches automatically.
 * For scheduled end-of-period cancellations see {@see SubscriptionScheduledCancel}.
 */
class SubscriptionCanceled extends CreemEvent
{
    //
}
