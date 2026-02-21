<?php

namespace Romansh\LaravelCreem\Events;

/**
 * Fired when a subscription enters a trial period.
 *
 * Corresponds to the "subscription.trialing" Creem webhook event.
 * The trial window is defined by "current_period_start_date" and
 * "current_period_end_date" inside {@see $object}.
 *
 * The {@see $object} array contains the full subscription resource with
 * status "trialing" and the "items" array with individual line items.
 *
 * Use this event to provision trial access and schedule reminder
 * notifications before the trial period ends.
 */
class SubscriptionTrialing extends CreemEvent
{
    //
}
