<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\IncompleteOrder;
use App\Models\LandingSection;
use App\Models\Order;
use App\Models\Product;
use App\Models\TrackingEvent;
use App\Services\OrderRiskScorer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, OrderRiskScorer $riskScorer): JsonResponse|RedirectResponse
    {
        $save = fn () => DB::transaction(fn () => $this->saveOrder($request, $riskScorer));

        return $request->filled('incomplete_token')
            ? Cache::lock('checkout:'.$request->input('incomplete_token'), 30)->block(10, $save)
            : $save();
    }

    private function saveOrder(StoreOrderRequest $request, OrderRiskScorer $riskScorer): JsonResponse|RedirectResponse
    {
        if ($request->filled('incomplete_token')) {
            $existing = Order::where('incomplete_token', $request->input('incomplete_token'))->first();
            if ($existing) {
                return $this->receipt($request, $existing);
            }
        }
        $data = $request->validated();
        $quantity = (int) $data['quantity'];
        $product = Product::query()->where('is_active', true)->findOrFail($data['product_id']);
        $unitPrice = $product->price;
        $orderDefaults = LandingSection::defaults('order');
        $orderSettings = LandingSection::where('slug', 'order')->value('content') ?? [];
        $deliveryKey = $data['delivery_area'] === 'inside_dhaka' ? 'inside_delivery_charge' : 'outside_delivery_charge';
        $deliveryCharge = max(0, (int) (filled($orderSettings[$deliveryKey] ?? null) ? $orderSettings[$deliveryKey] : $orderDefaults[$deliveryKey]));

        $risk = $riskScorer->assess($data, $request->ip());

        $order = Order::create([
            ...$risk,
            ...$data,
            'burger_type' => $product->name.' — ৳'.$product->price,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'delivery_area' => $data['delivery_area'],
            'delivery_charge' => $deliveryCharge,
            'total' => ($unitPrice * $quantity) + $deliveryCharge,
            'status' => $risk['risk_score'] >= config('order_risk.fake_threshold') ? 'fake' : 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        if ($order->status !== 'fake') TrackingEvent::create([
            'event_name' => 'order_completed',
            'visitor_hash' => hash('sha256', $request->ip().'|'.$request->userAgent()),
            'path' => '/',
            'occurred_on' => today()->toDateString(),
        ]);

        if ($request->filled('incomplete_token')) {
            IncompleteOrder::where('token', $request->string('incomplete_token'))->delete();
        }

        return $this->receipt($request, $order);
    }

    private function receipt(StoreOrderRequest $request, Order $order): JsonResponse|RedirectResponse
    {
        $message = 'Thank you! Your order has been received.';
        $request->session()->put('checkout_orders.'.$order->id, true);
        $addonUrl = URL::temporarySignedRoute('orders.modal-products.store', now()->addMinutes(30), ['order' => $order->id], absolute: false);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'order_id' => $order->id,
                'addon_url' => $addonUrl,
                'total' => $order->total,
                'tracking' => $order->status === 'fake' ? null : [
                    'transaction_id' => (string) $order->id, 'currency' => 'BDT',
                    'value' => $order->unit_price * $order->quantity, 'shipping' => $order->delivery_charge,
                    'items' => [['item_id' => (string) $order->product_id, 'item_name' => $order->product?->name ?? $order->burger_type,
                        'price' => $order->unit_price, 'quantity' => $order->quantity]],
                ],
            ], 201);
        }

        return back()->with('order_success', $message)->with('addon_url', $addonUrl);
    }
}
