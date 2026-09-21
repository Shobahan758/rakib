<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderNotificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $after = max(0, $request->integer('after'));
        $primaryOrders = Order::query()->whereNull('parent_order_id');

        if ($after === 0) {
            return response()->json([
                'latest_id' => (int) ((clone $primaryOrders)->max('id') ?? 0),
                'orders' => [],
            ]);
        }

        $orders = (clone $primaryOrders)
            ->where('id', '>', $after)
            ->oldest('id')
            ->limit(20)
            ->get(['id', 'name', 'phone', 'total', 'status', 'created_at']);

        return response()->json([
            'latest_id' => (int) ($orders->last()?->id ?? $after),
            'orders' => $orders->map(fn (Order $order): array => [
                'id' => $order->id,
                'name' => $order->name,
                'phone' => $order->phone,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at?->toIso8601String(),
                'url' => route('admin.orders.edit', ['order' => $order, 'return_to' => 'all']),
            ])->values(),
        ]);
    }
}
