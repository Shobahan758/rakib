<?php

return [
    'fake_threshold' => (int) env('ORDER_FAKE_RISK_THRESHOLD', 70),
    'max_orders_per_identity' => (int) env('ORDER_MAX_PER_IDENTITY', 5),
    'order_limit_window_days' => (int) env('ORDER_LIMIT_WINDOW_DAYS', 28),
    'failed_delivery_block_days' => (int) env('ORDER_FAILED_DELIVERY_BLOCK_DAYS', 28),
];
