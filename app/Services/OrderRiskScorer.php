<?php

namespace App\Services;

use App\Models\Order;

class OrderRiskScorer
{
    public function assess(array $customer, ?string $ip): array
    {
        $now = now();
        $phoneOrders = Order::query()->where('phone', $customer['phone']);
        $reasons = [];
        if ((clone $phoneOrders)->exists()) {
            $reasons['repeated_phone'] = 40;
        }
        if ($ip && Order::where('ip_address', $ip)->whereBetween('created_at', [$now->copy()->subMinutes(30), $now])->exists()) {
            $reasons['repeated_ip_30_minutes'] = 25;
        }
        if ((clone $phoneOrders)->whereBetween('created_at', [$now->copy()->subMinutes(2), $now])->exists()) {
            $reasons['rapid_repeat_2_minutes'] = 15;
        }
        $previous = (clone $phoneOrders)->latest('id')->first();
        if ($previous && ($this->normalize($previous->name) !== $this->normalize($customer['name'])
            || $this->normalize($previous->address) !== $this->normalize($customer['address']))) {
            $reasons['changed_customer_details'] = 20;
        }
        if ((clone $phoneOrders)->where(fn ($query) => $query->where('status', 'fake')->orWhereNotNull('fake_marked_at'))->exists()) {
            $reasons['previously_fake_phone'] = 100;
        }

        return ['risk_score' => array_sum($reasons), 'risk_reasons' => $reasons];
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($value)));
    }
}
