<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a refund is created by the merchant.
 *
 * Corresponds to the "refund.created" Creem webhook event.
 * The {@see $object} array contains the full refund resource, including
 * the refund amount and currency, reason, related transaction, subscription,
 * checkout, order, and customer objects.
 *
 * Use this event to update internal accounting records, notify the customer,
 * or adjust access rights if a refund implies cancellation.
 */
class RefundCreated extends CreemEvent
{
    //
}
