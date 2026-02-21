<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a customer opens a payment dispute (chargeback).
 *
 * Corresponds to the "dispute.created" Creem webhook event.
 * The {@see $object} array contains the full dispute resource, including
 * the disputed amount and currency, the related transaction (with status
 * "chargeback"), subscription, checkout, order, and customer objects.
 *
 * Use this event to flag the customer account for review, suspend access
 * if your policy requires it, or initiate your dispute-response workflow.
 */
class DisputeCreated extends CreemEvent
{
    //
}
