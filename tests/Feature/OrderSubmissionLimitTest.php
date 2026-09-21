<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderSubmissionLimitTest extends TestCase
{
    use DatabaseTransactions;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'order_risk.max_orders_per_identity' => 5,
            'order_risk.order_limit_window_hours' => 28,
        ]);
        $this->product = Product::create(['name' => 'Limit test product', 'price' => 990, 'is_active' => true]);
    }

    private function previous(string $phone, string $ip, array $overrides = []): Order
    {
        return Order::create([
            'name' => 'Previous Customer', 'phone' => $phone, 'address' => 'Dhaka test address',
            'product_id' => $this->product->id, 'burger_type' => $this->product->name,
            'quantity' => 1, 'unit_price' => 990, 'total' => 990,
            'status' => 'pending', 'ip_address' => $ip, ...$overrides,
        ]);
    }

    private function checkout(string $phone, string $ip)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->postJson(route('orders.store'), [
            'name' => 'Limit Customer', 'phone' => $phone, 'address' => 'Dhaka test address',
            'product_id' => $this->product->id, 'quantity' => 1, 'delivery_area' => 'inside_dhaka',
        ]);
    }

    public function test_sixth_order_from_the_same_phone_and_ip_is_rejected(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->previous('01712345678', '192.0.2.80');
        }

        $this->checkout('01712345678', '192.0.2.80')
            ->assertUnprocessable()
            ->assertJsonPath('errors.phone.0', 'একই মোবাইল নম্বর ও ডিভাইস/IP থেকে 28 ঘণ্টায় সর্বোচ্চ 5টি অর্ডার করা যাবে। 28 ঘণ্টা পর আবার অর্ডার করুন।');

        $this->assertSame(5, Order::where('phone', '01712345678')->count());
    }

    public function test_phone_or_ip_alone_does_not_share_the_order_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->previous('01812345678', '192.0.2.'.$i);
        }
        $this->checkout('01812345678', '192.0.2.90')->assertCreated();

        for ($i = 0; $i < 5; $i++) {
            $this->previous('0191234567'.$i, '192.0.2.91');
        }
        $this->checkout('01312345678', '192.0.2.91')->assertCreated();
    }

    public function test_orders_are_allowed_again_after_twenty_eight_hours(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->previous('01612345678', '192.0.2.70');
        }

        $this->travel(28)->hours()->travel(1)->minute();

        $this->checkout('01612345678', '192.0.2.70')->assertCreated();
    }

    public function test_add_on_products_do_not_consume_the_five_order_limit(): void
    {
        $parent = $this->previous('01312345678', '192.0.2.60');
        for ($i = 0; $i < 5; $i++) {
            $this->previous('0141234567'.$i, '192.0.2.61', ['parent_order_id' => $parent->id]);
        }

        $this->checkout('01512345678', '192.0.2.61')->assertCreated();
    }
}
