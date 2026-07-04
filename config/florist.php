<?php

return [
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'subscription_monthly_price' => env('PURE_ROSE_SUBSCRIPTION_MONTHLY', 350),
    'subscription_weekly_price' => env('PURE_ROSE_SUBSCRIPTION_WEEKLY', 120),
];
