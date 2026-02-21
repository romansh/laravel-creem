<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a payment fails for a subscription or transaction.
 *
 * Corresponds to the "payment.failed" Creem webhook event.
 */
class PaymentFailed extends CreemEvent
{
    //
}
