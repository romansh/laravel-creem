<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a new subscription is created and the first payment succeeds.
 *
 * Corresponds to the "subscription.active" Creem webhook event.
 * The {@see $object} array contains the full subscription resource,
 * including product and customer objects.
 *
 * NOTE: Creem recommends using {@see SubscriptionPaid} to activate access.
 * Use this event for synchronisation purposes only.
 */
class SubscriptionActive extends CreemEvent
{
    //
}
