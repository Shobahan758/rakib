<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderRiskScorer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderRiskScoringTest extends TestCase
{
    use DatabaseTransactions;

    private array $customer;
    private string $ip = '192.0.2.251';

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = ['name' => 'Risk Test', 'phone' => '019'.random_int(10000000, 99999999), 'address' => 'Dhaka Road 10'];
        $this->freezeTime();
    }

    private function previous(array $overrides = []): Order
    {
        return Order::create([
            ...$this->customer, 'quantity' => 1, 'unit_price' => 299, 'total' => 299,
            'status' => 'pending', 'ip_address' => $this->ip, ...$overrides,
        ]);
    }

    private function score(?string $ip = null): array
    {
        return app(OrderRiskScorer::class)->assess($this->customer, $ip);
    }

    public function test_new_customer_has_zero_risk(): void
    {
        $this->assertSame(['risk_score' => 0, 'risk_reasons' => []], $this->score());
    }

    public function test_repeated_phone_only_adds_forty_once(): void
    {
        $this->previous();
        $this->previous();
        $this->travel(31)->minutes();
        $this->assertSame(['risk_score' => 40, 'risk_reasons' => ['repeated_phone' => 40]], $this->score());
    }

    public function test_ip_window_includes_thirty_minutes_but_not_older_orders(): void
    {
        $this->previous(['phone' => '018'.random_int(10000000, 99999999)]);
        $this->travel(30)->minutes();
        $this->assertSame(25, $this->score($this->ip)['risk_score']);
        $this->travel(1)->seconds();
        $this->assertSame(0, $this->score($this->ip)['risk_score']);
    }

    public function test_rapid_repeat_window_is_two_minutes_for_the_same_phone(): void
    {
        $this->previous();
        $this->travel(2)->minutes();
        $this->assertSame(55, $this->score()['risk_score']);
        $this->travel(1)->seconds();
        $this->assertSame(40, $this->score()['risk_score']);
    }

    public function test_changed_name_or_address_adds_twenty_only_once(): void
    {
        $this->previous(['name' => 'Another Person', 'address' => 'Another address']);
        $this->travel(3)->minutes();
        $this->assertSame(60, $this->score()['risk_score']);
        $this->assertSame(20, $this->score()['risk_reasons']['changed_customer_details']);
    }

    public function test_spacing_and_case_changes_do_not_count_as_changed_details(): void
    {
        $this->previous(['name' => '  RISK   TEST ', 'address' => ' DHAKA  ROAD 10 ']);
        $this->assertArrayNotHasKey('changed_customer_details', $this->score()['risk_reasons']);
    }

    public function test_fake_history_is_remembered_after_manual_status_change(): void
    {
        $order = $this->previous();
        $order->update(['status' => 'fake']);
        $order->update(['status' => 'pending']);
        $this->travel(31)->minutes();
        $this->assertSame(140, $this->score()['risk_score']);
        $this->assertSame(100, $this->score()['risk_reasons']['previously_fake_phone']);
    }

    public function test_all_five_rules_add_up_to_two_hundred(): void
    {
        $this->previous(['name' => 'Another Person', 'status' => 'fake']);
        $this->assertSame(200, $this->score($this->ip)['risk_score']);
        $this->assertCount(5, $this->score($this->ip)['risk_reasons']);
    }

    public function test_checkout_below_threshold_stays_pending(): void
    {
        config(['order_risk.fake_threshold' => 70]);
        $this->previous();
        $this->travel(3)->minutes();
        $product = Product::create(['name' => 'Lower risk product', 'price' => 299, 'is_active' => true]);
        $response = $this->withServerVariables(['REMOTE_ADDR' => $this->ip])->postJson(route('orders.store'), [
            ...$this->customer, 'product_id' => $product->id, 'quantity' => 1, 'delivery_area' => 'inside_dhaka',
        ])->assertCreated();
        $order = Order::findOrFail($response->json('order_id'));
        $this->assertSame(65, $order->risk_score);
        $this->assertSame('pending', $order->status);
    }

    public function test_high_risk_checkout_saves_reasons_and_appears_in_fake_lists(): void
    {
        config(['order_risk.fake_threshold' => 70]);
        $this->previous();
        $product = Product::create(['name' => 'Risk checkout product', 'price' => 299, 'is_active' => true]);
        $response = $this->withServerVariables(['REMOTE_ADDR' => $this->ip])->postJson(route('orders.store'), [
            ...$this->customer, 'product_id' => $product->id, 'quantity' => 1,
            'delivery_area' => 'inside_dhaka', 'risk_score' => 0, 'status' => 'pending',
        ])->assertCreated();
        $order = Order::findOrFail($response->json('order_id'));
        $this->assertSame('fake', $order->status);
        $response->assertJsonPath('tracking', null);
        $this->assertSame(80, $order->risk_score);
        $this->assertCount(3, $order->risk_reasons);
        $this->assertNotNull($order->fake_marked_at);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        foreach (['all', 'today'] as $filter) {
            $this->get(route('admin.fake-orders.index', $filter))->assertOk()
                ->assertViewHas('orders', fn ($orders) => $orders->contains('id', $order->id))
                ->assertDontSee('Risk score')->assertDontSee('কারণ দেখুন');
        }
    }
}
