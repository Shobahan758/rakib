<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModalProductTest extends TestCase
{
    use DatabaseTransactions;

    public function test_modal_product_upload_management_and_recommendations(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get(route('admin.modal-products.create'))->assertOk()->assertSee('Create Modal');
        $image = UploadedFile::fake()->createWithContent('product.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        $data = ['name' => 'Modal test product', 'price' => 450, 'sort_order' => 1, 'is_active' => 1];
        $this->post(route('admin.modal-products.store'), [...$data, 'image' => $image])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.modal-products.index'));
        $product = Product::where('name', $data['name'])->firstOrFail();
        $this->assertTrue($product->is_modal_product);
        Storage::disk('public')->assertExists($product->image_path);
        $this->get(route('admin.modal-products.index'))->assertOk()
            ->assertViewHas('products', fn ($products) => $products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->perPage() === 10)
            ->assertSee($data['name']);
        $this->get(route('admin.products.index'))->assertOk()->assertDontSee($data['name']);
        $this->get(route('admin.modal-products.edit', $product))->assertOk();
        $this->get(route('admin.products.edit', $product))->assertNotFound();
        $this->get(route('home'))->assertOk()->assertSee('data-recommendation-id="'.$product->id.'"', false);
        $this->put(route('admin.modal-products.update', $product), [...$data, 'is_active' => 0])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.modal-products.index'));
        $this->get(route('home'))->assertOk()->assertDontSee('data-recommendation-id="'.$product->id.'"', false);
        $this->delete(route('admin.modal-products.destroy', $product))->assertRedirect();
        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing($product->image_path);
    }

    public function test_modal_products_require_product_or_site_settings_permission(): void
    {
        $this->get(route('admin.modal-products.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'manager', 'permissions' => []]));
        $this->get(route('admin.modal-products.index'))->assertForbidden();
        $this->post(route('admin.modal-products.store'), [])->assertForbidden();
    }
}
