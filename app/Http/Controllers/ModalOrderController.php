<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\LandingSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModalOrderController extends Controller
{
    public function store(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->session()->get('checkout_orders.'.$order->id), 403);
        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)->where('is_modal_product', true)],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        return DB::transaction(function () use ($request, $order, $data) {
            $parent = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless($parent->parent_order_id === null && in_array($parent->status, ['pending', 'fake'], true), 422, 'এই অর্ডারের ডেলিভারি প্রক্রিয়া শুরু হয়েছে। এখন আর পণ্য যোগ করা যাবে না।');
            $product = Product::where('is_active', true)->where('is_modal_product', true)->findOrFail($data['product_id']);
            $addon = Order::firstOrCreate(
                ['parent_order_id' => $parent->id, 'product_id' => $product->id],
                [
                    'name' => $parent->name, 'phone' => $parent->phone, 'email' => $parent->email,
                    'address' => $parent->address, 'delivery_area' => $parent->delivery_area,
                    'quantity' => $data['quantity'], 'unit_price' => $product->price,
                    'burger_type' => $product->name.' — ৳'.$product->price,
                    'delivery_charge' => 0, 'total' => $product->price * $data['quantity'],
                    'status' => $parent->status, 'risk_score' => $parent->risk_score, 'risk_reasons' => $parent->risk_reasons,
                    'ip_address' => $request->ip(), 'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                ],
            );
            $orderContent = array_replace(
                LandingSection::defaults('order'),
                LandingSection::where('slug', 'order')->value('content') ?? [],
            );

            return response()->json([
                'order_id' => $addon->id, 'parent_order_id' => $parent->id,
                'message' => $orderContent['modal_addon_success_message'],
                'delivery_total' => $parent->total + Order::where('parent_order_id', $parent->id)->sum('total'),
                'redirect_url' => URL::temporarySignedRoute(
                    'orders.modal-products.success',
                    now()->addMinutes(30),
                    ['order' => $parent->id, 'addon' => $addon->id],
                    absolute: false,
                ),
            ], 201);
        });
    }

    public function success(Request $request, Order $order, Order $addon): View
    {
        abort_unless($request->session()->get('checkout_orders.'.$order->id), 403);
        abort_unless($addon->parent_order_id === $order->id, 404);

        $orderContent = array_replace(
            LandingSection::defaults('order'),
            LandingSection::where('slug', 'order')->value('content') ?? [],
        );
        $siteContent = array_replace(
            LandingSection::defaults('site'),
            LandingSection::where('slug', 'site')->value('content') ?? [],
        );
        $deliveryTotal = $order->total + $order->deliveryAddons()->sum('total');

        return view('landing.order-success', compact('order', 'addon', 'orderContent', 'siteContent', 'deliveryTotal'));
    }
}
