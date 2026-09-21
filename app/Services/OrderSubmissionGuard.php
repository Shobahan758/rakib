<?php

namespace App\Services;

use App\Models\Order;

class OrderSubmissionGuard
{
    /** @return array{field: string, message: string}|null */
    public function violation(string $phone, ?string $ip): ?array
    {
        $maxOrders = max(1, (int) config('order_risk.max_orders_per_identity', 5));
        $windowHours = max(1, (int) config('order_risk.order_limit_window_hours', 28));
        $primaryOrders = Order::query()->whereNull('parent_order_id');

        if (! $ip) {
            return null;
        }

        $identityCount = (clone $primaryOrders)
            ->where('phone', $phone)
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHours($windowHours))
            ->count();

        if ($identityCount >= $maxOrders) {
            return [
                'field' => 'phone',
                'message' => "একই মোবাইল নম্বর ও ডিভাইস/IP থেকে {$windowHours} ঘণ্টায় সর্বোচ্চ {$maxOrders}টি অর্ডার করা যাবে। {$windowHours} ঘণ্টা পর আবার অর্ডার করুন।",
            ];
        }

        return null;
    }
}
