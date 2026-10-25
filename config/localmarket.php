<?php

// LocalMarket business settings. Values come from .env so they can be changed
// without touching code. See docs/requirements/ASSUMPTIONS_AND_OPEN_QUESTIONS.md.

return [
    'delivery_fee' => (float) env('DELIVERY_FEE', 4.95),
    'free_delivery_from' => (float) env('FREE_DELIVERY_FROM', 50),
    'delivery_postcodes' => array_filter(array_map('trim', explode(',', env('DELIVERY_POSTCODES', '')))),
    'payment_timeout_minutes' => (int) env('ORDER_PAYMENT_TIMEOUT_MINUTES', 30),
    'near_expiry_days' => (int) env('NEAR_EXPIRY_DAYS', 3),

    'payment_driver' => env('PAYMENT_DRIVER', 'mock'), // mock | stripe
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
];
