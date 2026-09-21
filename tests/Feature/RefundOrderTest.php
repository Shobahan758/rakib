<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RefundOrderTest extends TestCase
{
    use DatabaseTransactions;

    private function order(string $status = 'pending'): Order
    {
        return Order::create([
            'name' => 'Refund Customer',
            'phone' => '01712345678',
            'address' => 'Dhaka test address',
            'burger_type' => 'Furniture Polish',
            'quantity' => 1,
            'unit_price' => 850,
            'total' => 850,
            'status' => $status,
        ]);
    }

    public function test_refunded_orders_have_their_own_filter_and_sidebar_link(): void
    {
        $refunded = $this->order('refunded');
        $this->order('pending');

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->get(route('admin.orders.index', 'refunded'))
            ->assertOk()
            ->assertSee('Refunded Orders')
            ->assertSee('Refund')
            ->assertViewHas('orders', fn ($orders) => $orders->pluck('id')->all() === [$refunded->id]);
    }

    public function test_order_status_can_be_changed_to_refunded(): void
    {
        $order = $this->order();

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->patch(route('admin.orders.status', $order), ['status' => 'refunded'])
            ->assertRedirect(route('admin.orders.index', 'refunded'));

        $this->assertSame('refunded', $order->fresh()->status);
    }
}
