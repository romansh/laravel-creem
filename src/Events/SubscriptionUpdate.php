<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a subscription object is updated.
 *
 * Corresponds to the "subscription.update" Creem webhook event.
 * Triggered by any change to the subscription, such as a plan upgrade,
 * item quantity change, or metadata update.
 *
 * The {@see $object} array contains the full updated subscription resource,
 * including the "items" array with individual subscription line items.
 */
class SubscriptionUpdate extends CreemEvent
{
    //
}
