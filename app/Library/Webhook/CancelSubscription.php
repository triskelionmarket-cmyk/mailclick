<?php

namespace Acelle\Library\Webhook;

class CancelSubscription
{
    public const EVENT = 'cancel_subscription';

    public static function run($customerId, $planId)
    {
        // Get all webhooks
        $webhooks = \Acelle\Model\Webhook::active()->where('event', static::EVENT)->get();

        // Run all
        foreach ($webhooks as $webhook) {
            $webhook->run([
                'customer_id' => $customerId,
                'plan_id' => $planId,
            ]);
        }
    }
}
