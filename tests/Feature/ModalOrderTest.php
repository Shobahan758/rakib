<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ModalOrderTest extends TestCase
{
    use DatabaseTransactions;

    private function product(bool $modal): Product
    {
        return Product::create(['name' => $modal ? 'Modal only headphones' : 'Regular burger', 'price' => 666, 'is_active' => true, 'is_modal_product' => $modal]);
    }

    private function checkout(Product $product): array
    {
        return $this->postJson(route('orders.store'), ['name' => 'Modal Checkout', 'phone' => '019'.random_int(10000000, 99999999),
            'address' => 'Dhaka Road 10', 'quantity' => 1, 'delivery_area' => 'inside_dhaka', 'product_id' => $product->id])->assertCreated()->json();
    }

    public function test_modal_product_is_only_in_success_dialog_not_main_picker(): void
    {
        $product = $this->product(true);
        $html = $this->get(route('home'))->assertOk()->getContent();
        $beforeModal = explode('<dialog id="orderSuccessModal"', $html)[0];
        $this->assertStringNotContainsString($product->name, $beforeModal);
        $this->assertStringContainsString($product->name, $html);
        $this->postJson(route('orders.store'), ['name' => 'Test Customer', 'phone' => '01712345678', 'address' => 'Dhaka Road 10',
            'quantity' => 1, 'delivery_area' => 'inside_dhaka', 'product_id' => $product->id])->assertUnprocessable()->assertJsonValidationErrors('product_id');
    }

    public function test_modal_purchase_shares_delivery_and_is_idempotent(): void
    {
        $normal = $this->product(false);
        $modal = $this->product(true);
        $receipt = $this->checkout($normal);
        $parent = Order::findOrFail($receipt['order_id']);
        $payload = ['product_id' => $modal->id, 'quantity' => 2, 'price' => 1, 'phone' => '000', 'delivery_charge' => 999];
        $response = $this->postJson($receipt['addon_url'], $payload)->assertCreated();
        $addon = Order::findOrFail($response->json('order_id'));
        $this->assertSame($parent->id, $addon->parent_order_id);
        $this->assertSame($parent->phone, $addon->phone);
        $this->assertSame($parent->address, $addon->address);
        $this->assertSame($parent->status, $addon->status);
        $this->assertSame(0, $addon->delivery_charge);
        $this->assertSame(1332, $addon->total);
        $response->assertJsonPath('delivery_total', $parent->total + 1332);
        $this->get($response->json('redirect_url'))
            ->assertOk()
            ->assertSee($modal->name)
            ->assertDontSee('{{', false);
        $this->postJson($receipt['addon_url'], $payload)->assertCreated()->assertJsonPath('order_id', $addon->id);
        $this->assertSame(1, $parent->deliveryAddons()->count());
        $parent->update(['status' => 'shipping', 'address' => 'Updated delivery address']);
        $this->assertSame('shipping', $addon->fresh()->status);
        $this->assertSame('Updated delivery address', $addon->fresh()->address);
    }

    public function test_checkout_and_addons_use_relative_urls_behind_an_https_proxy(): void
    {
        URL::forceRootUrl('http://internal.example.test');
        try {
            $normal = $this->product(false);
            $modal = $this->product(true);
            $this->get('https://shop.example.test/')->assertOk()
                ->assertSee('action="/orders"', false)
                ->assertSee('data-incomplete-action="/incomplete-orders"', false);
            $receipt = $this->postJson('https://shop.example.test/orders', [
                'name' => 'Proxy Checkout', 'phone' => '019'.random_int(10000000, 99999999),
                'address' => 'Dhaka Road 10', 'quantity' => 1,
                'delivery_area' => 'inside_dhaka', 'product_id' => $normal->id,
            ])->assertCreated()->json();
            $this->assertStringStartsWith('/orders/', $receipt['addon_url']);
            $payload = ['product_id' => $modal->id, 'quantity' => 1];
            $this->postJson('http://shop.example.test'.$receipt['addon_url'], $payload)->assertCreated();
            $this->postJson('http://shop.example.test'.$receipt['addon_url'].'&extra=tampered', $payload)->assertForbidden();
        } finally {
            URL::forceRootUrl(null);
        }
    }

    public function test_modal_purchase_requires_owner_session_signature_and_eligible_order(): void
    {
        $normal = $this->product(false);
        $modal = $this->product(true);
        $receipt = $this->checkout($normal);
        $payload = ['product_id' => $modal->id, 'quantity' => 1];
        $this->postJson(route('orders.modal-products.store', $receipt['order_id']), $payload)->assertForbidden();
        $this->postJson($receipt['addon_url'], ['product_id' => $normal->id, 'quantity' => 1])->assertUnprocessable();
        $modal->update(['is_active' => false]);
        $this->postJson($receipt['addon_url'], $payload)->assertUnprocessable();
        $modal->update(['is_active' => true]);
        Order::whereKey($receipt['order_id'])->update(['status' => 'delivered']);
        $this->postJson($receipt['addon_url'], $payload)->assertUnprocessable();
        $this->flushSession();
        $this->postJson($receipt['addon_url'], $payload)->assertForbidden();
    }
}
