<?php

return [
    'new_subscription' => [
        'params' => [
            'customer_id',
            'plan_id',
        ],
    ],

    'cancel_subscription' => [
        'params' => [
            'customer_id',
            'plan_id',
        ],
    ],

    'new_customer' => [
        'params' => [
            'customer_id',
        ],
    ],

    'change_plan' => [
        'params' => [
            'customer_id',
            'old_plan_id',
            'new_plan_id',
        ],
    ],

    'terminate_subscription' => [
        'params' => [
            'customer_id',
            'plan_id',
        ],
    ],

    'automation_webhook' => [
        'params' => [
            'automation_id',
        ],
    ],
];
