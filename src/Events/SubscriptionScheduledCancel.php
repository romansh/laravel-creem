<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a subscription is scheduled to cancel at the end of the current billing period.
 *
 * Corresponds to the "subscription.scheduled_cancel" Creem webhook event.
 * The subscription remains active until the date in "current_period_end_date"
 * inside {@see $object}, after which it transitions to "canceled".
 *
 * The {@see $object} array contains the full subscription resource with
 * status "scheduled_cancel" and all billing-period details.
 *
 * Use this event to notify the customer of the upcoming cancellation or to
 * present retention incentives. The subscription can be resumed before the
 * period ends via the Creem Resume Subscription API endpoint.
 */
class SubscriptionScheduledCancel extends CreemEvent
{
    //
}
