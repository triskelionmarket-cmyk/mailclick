<?php

namespace Acelle\Library\Webhook;

class ChangePlan
{
    public const EVENT = 'change_plan';

    public static function run($customerId, $oldPlanId, $newPlanId)
    {
        // Get all webhooks
        $webhooks = \Acelle\Model\Webhook::active()->where('event', static::EVENT)->get();

        // Run all
        foreach ($webhooks as $webhook) {
            $webhook->run([
                'customer_id' => $customerId,
                'old_plan_id' => $oldPlanId,
                'new_plan_id' => $newPlanId,
            ]);
        }
    }
}
