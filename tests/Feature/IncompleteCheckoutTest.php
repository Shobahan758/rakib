<?php

namespace Tests\Feature;

use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class IncompleteCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    private function draft(): array
    {
        return ['token' => (string) Str::uuid(), 'name' => 'Draft Customer', 'phone' => '+৮৮০১৭১২৩৪৫৬৭৮'];
    }

    public function test_name_and_phone_save_without_address_and_update_one_entry(): void
    {
        $data = $this->draft();
        $this->postJson(route('incomplete-orders.store'), $data)->assertOk()->assertJsonPath('saved', true);
        $this->assertDatabaseHas('incomplete_orders', ['token' => $data['token'], 'phone' => '01712345678', 'address' => null]);
        $this->postJson(route('incomplete-orders.store'), [...$data, 'address' => 'Dhaka Road 10', 'quantity' => 300])->assertOk();
        $this->assertSame(1, IncompleteOrder::where('token', $data['token'])->count());
        $draft = IncompleteOrder::where('token', $data['token'])->firstOrFail();
        $this->assertSame(300, $draft->quantity);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        foreach (['all', 'today'] as $filter) {
            $this->get(route('admin.incomplete-orders.index', $filter))->assertOk()
                ->assertViewHas('orders', fn ($orders) => $orders->contains('id', $draft->id));
        }

        $this->get(route('admin.incomplete-orders.edit', ['order' => $draft, 'filter' => 'all']))
            ->assertOk()
            ->assertViewHas('order', fn (IncompleteOrder $order) => $order->is($draft));
        $this->get(route('admin.incomplete-orders.legacy-edit', $draft))
            ->assertOk()
            ->assertViewHas('order', fn (IncompleteOrder $order) => $order->is($draft));
    }

    public function test_completed_checkout_removes_draft_and_ignores_late_autosave(): void
    {
        $data = $this->draft();
        $this->postJson(route('incomplete-orders.store'), $data)->assertOk();
        $product = Product::create(['name' => 'Draft checkout product', 'price' => 299, 'is_active' => true]);
        $checkout = [...$data, 'incomplete_token' => $data['token'], 'product_id' => $product->id,
            'quantity' => 1, 'delivery_area' => 'inside_dhaka', 'address' => 'Dhaka Road 10'];
        $response = $this->postJson(route('orders.store'), $checkout)->assertCreated();
        $this->assertDatabaseMissing('incomplete_orders', ['token' => $data['token']]);
        $this->postJson(route('incomplete-orders.store'), $data)->assertOk()->assertJsonPath('completed', true);
        $this->assertDatabaseMissing('incomplete_orders', ['token' => $data['token']]);
        $this->postJson(route('orders.store'), $checkout)->assertCreated()->assertJsonPath('order_id', $response->json('order_id'));
        $this->assertSame(1, Order::where('incomplete_token', $data['token'])->count());
    }

    public function test_failed_checkout_keeps_draft_and_invalid_phone_does_not_save(): void
    {
        $data = $this->draft();
        $this->postJson(route('incomplete-orders.store'), [...$data, 'phone' => '123'])->assertUnprocessable();
        $this->assertDatabaseMissing('incomplete_orders', ['token' => $data['token']]);
        $this->postJson(route('incomplete-orders.store'), $data)->assertOk();
        $this->postJson(route('orders.store'), [...$data, 'incomplete_token' => $data['token']])->assertUnprocessable();
        $this->assertDatabaseHas('incomplete_orders', ['token' => $data['token']]);
    }
}
