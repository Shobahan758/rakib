<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminOrderNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private function user(array $permissions): User
    {
        return User::factory()->create(['role' => 'manager', 'permissions' => $permissions]);
    }

    private function order(array $overrides = []): Order
    {
        return Order::create([
            'name' => 'Notification Customer',
            'phone' => '01712345678',
            'address' => 'Dhaka test address',
            'burger_type' => 'Furniture Polish',
            'quantity' => 1,
            'unit_price' => 850,
            'total' => 850,
            'status' => 'pending',
            ...$overrides,
        ]);
    }

    public function test_initial_request_sets_a_baseline_without_replaying_old_orders(): void
    {
        $order = $this->order();

        $this->actingAs($this->user(['orders']))
            ->getJson(route('admin.order-notifications'))
            ->assertOk()
            ->assertJsonPath('latest_id', $order->id)
            ->assertJsonCount(0, 'orders');
    }

    public function test_orders_after_the_cursor_are_returned_without_add_on_orders(): void
    {
        $old = $this->order();
        $new = $this->order(['phone' => '01812345678']);
        $this->order(['phone' => '01912345678', 'parent_order_id' => $new->id]);

        $this->actingAs($this->user(['orders']))
            ->getJson(route('admin.order-notifications', ['after' => $old->id]))
            ->assertOk()
            ->assertJsonPath('latest_id', $new->id)
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.id', $new->id)
            ->assertJsonPath('orders.0.phone', '01812345678')
            ->assertJsonPath('orders.0.total', 850);
    }

    public function test_order_permission_is_required(): void
    {
        $this->actingAs($this->user(['dashboard']))
            ->getJson(route('admin.order-notifications'))
            ->assertForbidden();
    }
}
