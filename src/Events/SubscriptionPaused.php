<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a subscription is paused.
 *
 * Corresponds to the "subscription.paused" Creem webhook event.
 * The {@see $object} array contains the full subscription resource with
 * status "paused", billing-period boundaries, and the "items" array
 * with individual subscription line items.
 *
 * Use this event to temporarily suspend a customer's access to your
 * product while the subscription remains in a paused state.
 */
class SubscriptionPaused extends CreemEvent
{
    //
}
