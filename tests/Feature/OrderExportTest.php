<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderExportTest extends TestCase
{
    use DatabaseTransactions;

    private function order(int $number, string $status = 'pending'): Order
    {
        return Order::create([
            'name' => $number === 1 ? '=Unsafe Customer' : 'Customer '.$number,
            'phone' => '017123456'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            'address' => 'Dhaka test address',
            'burger_type' => 'Furniture Polish',
            'quantity' => 1,
            'unit_price' => 850,
            'total' => 850,
            'status' => $status,
        ]);
    }

    public function test_view_all_shows_every_filtered_order_without_pagination(): void
    {
        foreach (range(1, 12) as $number) {
            $this->order($number);
        }

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->get(route('admin.orders.index', ['filter' => 'all', 'view_all' => 1]))
            ->assertOk()
            ->assertSee('Show 10')
            ->assertViewHas('orders', fn ($orders) => $orders->count() === 12 && $orders->perPage() === 12);
    }

    public function test_excel_download_contains_all_filtered_orders_and_escapes_formulas(): void
    {
        $pending = $this->order(1);
        $this->order(2, 'delivered');

        $response = $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->get(route('admin.orders.export', 'all'))
            ->assertOk()
            ->assertDownload();

        $content = $response->streamedContent();
        $this->assertStringContainsString((string) $pending->id, $content);
        $this->assertStringContainsString("'=Unsafe Customer", $content);
        $this->assertStringNotContainsString('Customer 2', $content);
    }
}
