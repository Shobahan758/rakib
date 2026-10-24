<?php

namespace Tests\Feature;

use App\Models\CourierBooking;
use App\Models\CourierSetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CourierTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    private function order(array $attributes = []): Order
    {
        return Order::create(array_merge(['name' => 'Test Customer', 'phone' => '+8801712345678', 'address' => 'Road 1, Dhaka, Bangladesh',
            'burger_type' => 'Furniture Polish', 'quantity' => 1, 'unit_price' => 990, 'total' => 1050, 'status' => 'pending'], $attributes));
    }

    private function setting(string $provider = 'steadfast'): CourierSetting
    {
        return CourierSetting::create(['provider' => $provider, 'credentials' => match ($provider) {
            'steadfast' => ['api_key' => 'secret-api-key', 'secret_key' => 'secret-secret-key'],
            'pathao' => ['client_id' => '123', 'client_secret' => 'pathao-secret', 'username' => 'merchant@example.com', 'password' => 'password', 'store_id' => '5', 'environment' => 'sandbox'],
            'redx' => ['access_token' => 'redx-secret', 'store_id' => '12', 'environment' => 'sandbox'],
        }]);
    }

    public function test_settings_are_encrypted_preserved_and_not_rendered(): void
    {
        $this->actingAs($this->admin())->put(route('admin.couriers.save'), ['steadfast' => ['api_key' => 'secret-api-key', 'secret_key' => 'secret-secret-key'],
            'pathao' => ['environment' => 'live'], 'redx' => ['environment' => 'live']])->assertSessionHasNoErrors();
        $this->assertStringNotContainsString('secret-api-key', DB::table('courier_settings')->where('provider', 'steadfast')->value('credentials'));
        $this->get(route('admin.couriers.settings'))->assertOk()->assertDontSee('secret-api-key');
        $this->put(route('admin.couriers.save'), ['steadfast' => ['api_key' => '', 'secret_key' => ''],
            'pathao' => ['environment' => 'live'], 'redx' => ['environment' => 'live']])->assertSessionHasNoErrors();
        $this->assertSame('secret-api-key', CourierSetting::where('provider', 'steadfast')->first()->credentials['api_key']);
    }

    public function test_single_provider_save_and_test_uses_new_credentials(): void
    {
        Http::fake(['*' => Http::response(['status' => 200, 'current_balance' => 0])]);
        $this->actingAs($this->admin())->put(route('admin.couriers.save'), [
            'provider' => 'steadfast', 'action' => 'test',
            'steadfast' => ['api_key' => 'demo-api', 'secret_key' => 'demo-secret'],
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        Http::assertSent(fn ($request) => $request->method() === 'GET' && $request->hasHeader('Api-Key', 'demo-api') && $request->hasHeader('Secret-Key', 'demo-secret'));
        $this->assertDatabaseCount('courier_settings', 1);
        $this->get(route('admin.couriers.settings'))->assertOk()->assertSee('Save & Test Connection', false)->assertDontSee('demo-secret');
    }

    public function test_pathao_and_redx_save_then_test_with_demo_api_responses(): void
    {
        $this->actingAs($this->admin());
        foreach (['pathao', 'redx'] as $provider) {
            $credentials = $this->setting($provider)->credentials;
            Http::fake([
                '*/issue-token' => Http::response(['access_token' => 'demo-token']),
                '*/stores' => Http::response(['code' => 200, 'data' => ['data' => []], 'pickup_stores' => []]),
            ]);
            $this->put(route('admin.couriers.save'), ['provider' => $provider, 'action' => 'test', $provider => $credentials])
                ->assertSessionHasNoErrors()->assertSessionHas('success');
            Http::assertSent(fn ($request) => $request->method() === 'GET' && $request->hasHeader('Authorization', $provider === 'pathao' ? 'Bearer demo-token' : 'Bearer redx-secret'));
        }
    }

    public function test_incomplete_credentials_explain_missing_fields_without_calling_api(): void
    {
        Http::fake();
        $this->actingAs($this->admin())->put(route('admin.couriers.save'), [
            'provider' => 'steadfast', 'action' => 'test', 'steadfast' => ['api_key' => 'demo-api'],
        ])->assertSessionHas('error', fn ($message) => str_contains($message, 'Secret Key'));
        Http::assertNothingSent();
    }

    public function test_invalid_credentials_are_not_flashed_into_session(): void
    {
        $this->actingAs($this->admin())->put(route('admin.couriers.save'), [
            'provider' => 'pathao', 'action' => 'test',
            'pathao' => ['environment' => 'live', 'username' => 'invalid-email', 'password' => 'demo-private-password'],
        ])->assertSessionHasErrors('pathao.username')->assertSessionMissing('_old_input');
        $this->assertDatabaseCount('courier_settings', 0);
    }

    public function test_settings_require_super_admin_and_booking_requires_orders_permission(): void
    {
        $order = $this->order();
        $this->get(route('admin.couriers.settings'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'admin', 'permissions' => []]))
            ->get(route('admin.couriers.settings'))->assertForbidden();
        $this->post(route('admin.couriers.store', $order), ['provider' => 'steadfast'])->assertForbidden();
    }

    public function test_steadfast_booking_collects_addon_total_and_prevents_duplicates(): void
    {
        $this->setting();
        $order = $this->order();
        $this->order(['parent_order_id' => $order->id, 'total' => 200]);
        Http::fake(['*' => Http::response(['status' => 200, 'consignment' => ['consignment_id' => 123, 'tracking_code' => 'ABC']], 200)]);
        $this->actingAs($this->admin())->get(route('admin.couriers.book', $order))->assertOk()->assertSee('1,250');
        $this->post(route('admin.couriers.store', $order), ['provider' => 'steadfast'])->assertSessionHas('success');
        Http::assertSent(fn ($request) => $request['cod_amount'] === 1250 && $request['recipient_phone'] === '01712345678' && $request->hasHeader('Api-Key', 'secret-api-key'));
        $this->assertSame('shipping', $order->fresh()->status);
        $this->post(route('admin.couriers.store', $order), ['provider' => 'steadfast'])->assertSessionHas('error');
        Http::assertSentCount(1);
        $this->assertDatabaseHas('courier_bookings', ['order_id' => $order->id, 'consignment_id' => '123', 'status' => 'booked']);
    }

    public function test_timeout_is_not_retried_and_uncertain_booking_is_blocked(): void
    {
        $this->setting();
        $order = $this->order();
        Http::fake(['*' => Http::failedConnection()]);
        $this->actingAs($this->admin())->post(route('admin.couriers.store', $order), ['provider' => 'steadfast'])->assertSessionHas('error');
        $this->assertDatabaseHas('courier_bookings', ['order_id' => $order->id, 'status' => 'unknown']);
        $this->assertSame('pending', $order->fresh()->status);
        Http::fake(['*' => Http::response([])]);
        $this->post(route('admin.couriers.store', $order), ['provider' => 'steadfast'])->assertSessionHas('error');
        Http::assertNothingSent();
    }

    public function test_rejected_booking_can_be_corrected_and_retried(): void
    {
        $this->setting();
        $order = $this->order();
        Http::fakeSequence()->push(['status' => 422], 422)->push(['status' => 200, 'consignment' => ['consignment_id' => 456]]);
        $this->actingAs($this->admin())->post(route('admin.couriers.store', $order), ['provider' => 'steadfast'])->assertSessionHas('error');
        $this->assertDatabaseHas('courier_bookings', ['order_id' => $order->id, 'status' => 'failed']);
        $this->post(route('admin.couriers.store', $order), ['provider' => 'steadfast'])->assertSessionHas('success');
        $this->assertSame(1, CourierBooking::count());
    }

    public function test_test_connection_does_not_create_a_delivery(): void
    {
        $this->setting();
        Http::fake(['*' => Http::response(['status' => 200, 'current_balance' => 0])]);
        $this->actingAs($this->admin())->post(route('admin.couriers.test', 'steadfast'))->assertSessionHas('success');
        Http::assertSent(fn ($request) => $request->method() === 'GET' && str_ends_with($request->url(), '/get_balance'));
        $this->assertDatabaseCount('courier_bookings', 0);
    }

    public function test_pathao_requires_location_and_uses_sandbox_token(): void
    {
        $this->setting('pathao');
        $order = $this->order();
        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'access-token']),
            '*/orders' => Http::response(['data' => ['consignment_id' => 'PATH123']], 201),
        ]);
        $this->actingAs($this->admin())->post(route('admin.couriers.store', $order), ['provider' => 'pathao'])->assertSessionHasErrors(['weight', 'city_id', 'zone_id']);
        Http::assertNothingSent();
        $this->post(route('admin.couriers.store', $order), ['provider' => 'pathao', 'weight' => 1.5, 'city_id' => 1, 'zone_id' => 2])->assertSessionHas('success');
        Http::assertSent(fn ($request) => str_contains($request->url(), 'courier-api-sandbox.pathao.com/aladdin/api/v1/orders') && $request['item_weight'] === 1.5 && $request['amount_to_collect'] === 1050 && $request->hasHeader('Authorization', 'Bearer access-token'));
    }

    public function test_redx_weight_is_sent_in_grams(): void
    {
        $this->setting('redx');
        $order = $this->order();
        Http::fake(['*' => Http::response(['tracking_id' => 'RED123'])]);
        $this->actingAs($this->admin())->post(route('admin.couriers.store', $order), ['provider' => 'redx', 'weight' => 1.5, 'area_id' => 10, 'area_name' => 'Dhaka'])->assertSessionHas('success');
        Http::assertSent(fn ($request) => $request['parcel_weight'] === 1500 && $request['pickup_store_id'] === 12 && str_contains($request->url(), 'sandbox.redx.com.bd'));
    }

    public function test_delivery_locations_are_available_without_exposing_credentials(): void
    {
        $this->setting('pathao');
        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'access-token']),
            '*/city-list' => Http::response(['data' => ['data' => [['city_id' => 1, 'city_name' => 'Dhaka']]]]),
        ]);
        $this->actingAs($this->admin())->getJson(route('admin.couriers.locations', ['provider' => 'pathao']))
            ->assertOk()->assertJsonPath('locations.0.name', 'Dhaka')->assertDontSee('access-token');
        $this->getJson(route('admin.couriers.locations', ['provider' => 'pathao', 'city' => '../orders']))->assertUnprocessable();
    }

    public function test_booking_record_prevents_order_deletion(): void
    {
        $order = $this->order();
        CourierBooking::create(['order_id' => $order->id, 'provider' => 'steadfast', 'status' => 'booked', 'cod_amount' => $order->total]);
        $this->actingAs($this->admin())->delete(route('admin.orders.destroy', $order), ['return_to' => 'all'])->assertUnprocessable();
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_unconfigured_or_child_order_never_reaches_booking_api(): void
    {
        Http::fake();
        $order = $this->order();
        $this->actingAs($this->admin())->post(route('admin.couriers.store', $order), ['provider' => 'steadfast'])->assertSessionHas('error');
        $this->setting();
        $child = $this->order(['parent_order_id' => $order->id]);
        $this->post(route('admin.couriers.store', $child), ['provider' => 'steadfast'])->assertSessionHas('error');
        Http::assertNothingSent();
    }
}
