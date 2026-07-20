<?php

namespace Acelle\Library\Webhook;

class NewSubscription
{
    public const EVENT = 'new_subscription';

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
