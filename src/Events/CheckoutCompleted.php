<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a checkout session is successfully completed.
 *
 * Corresponds to the "checkout.completed" Creem webhook event.
 * The {@see $object} array contains the full checkout resource,
 * including order, product, customer, subscription, custom fields,
 * and metadata.
 *
 * Typical use-cases: send a purchase confirmation e-mail, provision
 * initial access, or create an internal order record.
 */
class CheckoutCompleted extends CreemEvent
{
    //
}
