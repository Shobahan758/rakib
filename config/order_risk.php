<?php

return [
    'fake_threshold' => (int) env('ORDER_FAKE_RISK_THRESHOLD', 70),
    'max_orders_per_identity' => (int) env('ORDER_MAX_PER_IDENTITY', 5),
    'order_limit_window_hours' => (int) env('ORDER_LIMIT_WINDOW_HOURS', 28),
];
