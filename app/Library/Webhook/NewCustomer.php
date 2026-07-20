<?php

namespace Acelle\Library\Webhook;

class NewCustomer
{
    public const EVENT = 'new_customer';

    public static function run($customerId)
    {
        // Get all webhooks
        $webhooks = \Acelle\Model\Webhook::active()->where('event', static::EVENT)->get();

        // Run all
        foreach ($webhooks as $webhook) {
            $webhook->run([
                'customer_id' => $customerId,
            ]);
        }
    }
}
