<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_repeat_customer_orders_are_saved_and_visible_in_todays_orders(): void
    {
        $product = Product::create(['name' => 'Checkout regression product', 'price' => 299, 'is_active' => true, 'sort_order' => 1]);
        $customer = 'Checkout '.bin2hex(random_bytes(6));
        $ids = [];
        $this->travelTo(now()->startOfDay());
        for ($i = 0; $i < 3; $i++) {
            $this->travel(31)->minutes();
            $response = $this->postJson(route('orders.store'), [
                'name' => $customer, 'phone' => '01712345678', 'address' => 'Dhaka test address',
                'product_id' => $product->id, 'quantity' => 2, 'delivery_area' => 'inside_dhaka',
                'unit_price' => 1, 'total' => 1,
            ])->assertCreated();
            $id = $response->json('order_id');
            $ids[] = $id;
            $response->assertJsonPath('tracking.transaction_id', (string) $id)->assertJsonPath('tracking.currency', 'BDT');
            $order = Order::findOrFail($id);
            $this->assertSame('pending', $order->status);
            $this->assertSame(598 + $order->delivery_charge, $order->total);
            $this->assertSame($order->total, $response->json('total'));
        }
        $this->travelBack();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        foreach (['all', 'today'] as $filter) {
            $this->get(route('admin.orders.index', $filter))->assertOk()
                ->assertViewHas('orders', fn ($orders) => $orders instanceof \Illuminate\Pagination\LengthAwarePaginator
                    && $orders->perPage() === 10
                    && collect($ids)->diff($orders->pluck('id'))->isEmpty())
                ->assertSee('<th>Product</th>', false)
                ->assertDontSee('<th>Burger</th>', false)
                ->assertDontSee('Risk score')
                ->assertDontSee('<th>Date</th>', false);
        }

        $this->get(route('admin.incomplete-orders.index', 'all'))->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders instanceof \Illuminate\Pagination\LengthAwarePaginator
                && $orders->perPage() === 10);
    }

    public function test_inactive_product_cannot_create_an_order(): void
    {
        $product = Product::create(['name' => 'Inactive checkout product', 'price' => 299, 'is_active' => false, 'sort_order' => 1]);
        $this->postJson(route('orders.store'), [
            'name' => 'Checkout test', 'phone' => '01712345678', 'address' => 'Dhaka test address',
            'product_id' => $product->id, 'quantity' => 1, 'delivery_area' => 'inside_dhaka',
        ])->assertUnprocessable()->assertJsonValidationErrors('product_id');
        $this->assertFalse(Order::where('product_id', $product->id)->exists());
    }
}
