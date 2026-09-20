<?php

namespace App\Services;

use App\Models\Order;

class OrderSubmissionGuard
{
    /** @return array{field: string, message: string}|null */
    public function violation(string $phone, ?string $ip): ?array
    {
        $maxOrders = max(1, (int) config('order_risk.max_orders_per_identity', 5));
        $windowDays = max(1, (int) config('order_risk.order_limit_window_days', 28));
        $failedDeliveryDays = max(1, (int) config('order_risk.failed_delivery_block_days', 28));
        $primaryOrders = Order::query()->whereNull('parent_order_id');

        $hasRecentFailedDelivery = (clone $primaryOrders)
            ->where('phone', $phone)
            ->where(function ($query) use ($failedDeliveryDays) {
                $cutoff = now()->subDays($failedDeliveryDays);
                $query->where(function ($fake) use ($cutoff) {
                    $fake->where('status', 'fake')->where('created_at', '>=', $cutoff);
                })->orWhere('fake_marked_at', '>=', $cutoff);
            })
            ->exists();

        if ($hasRecentFailedDelivery) {
            return [
                'field' => 'phone',
                'message' => "এই নম্বরের আগের অর্ডারের ডেলিভারি গ্রহণ করা হয়নি। {$failedDeliveryDays} দিন পর আবার চেষ্টা করুন অথবা আমাদের ফোন করুন।",
            ];
        }

        $windowStart = now()->subDays($windowDays);
        $phoneCount = (clone $primaryOrders)->where('phone', $phone)->where('created_at', '>=', $windowStart)->count();
        if ($phoneCount >= $maxOrders) {
            return [
                'field' => 'phone',
                'message' => "একটি মোবাইল নম্বর থেকে {$windowDays} দিনে সর্বোচ্চ {$maxOrders}টি অর্ডার করা যাবে।",
            ];
        }

        if ($ip) {
            $ipCount = (clone $primaryOrders)->where('ip_address', $ip)->where('created_at', '>=', $windowStart)->count();
            if ($ipCount >= $maxOrders) {
                return [
                    'field' => 'phone',
                    'message' => "একটি নেটওয়ার্ক/IP থেকে {$windowDays} দিনে সর্বোচ্চ {$maxOrders}টি অর্ডার করা যাবে।",
                ];
            }
        }

        return null;
    }
}
