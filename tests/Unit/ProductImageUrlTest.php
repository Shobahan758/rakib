<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUrlTest extends TestCase
{
    public function test_picker_name_separates_the_package_from_its_details(): void
    {
        $product = new Product(['name' => '📦প্রয়োজনীয় প্যাক (300ml) 🪑 ৩–৪টি ফার্নিচারের জন্য']);

        $this->assertSame('📦 প্রয়োজনীয় প্যাক (300ml)', $product->pickerPackageName());
        $this->assertSame('🪑 ৩–৪টি ফার্নিচারের জন্য', $product->pickerPackageDetails());

        $basic = new Product(['name' => '✨ বেসিক পলিশ প্যাক 500ml (ডেলিভারি চার্জ ফ্রি)']);
        $this->assertSame('✨ বেসিক পলিশ প্যাক 500ml', $basic->pickerPackageName());
        $this->assertSame('(ডেলিভারি চার্জ ফ্রি)', $basic->pickerPackageDetails());
    }

    public function test_product_prices_are_cast_to_integers(): void
    {
        $product = new Product(['price' => '990', 'regular_price' => '1350']);

        $this->assertSame(990, $product->price);
        $this->assertSame(1350, $product->regular_price);
    }

    public function test_regular_price_uses_saved_value_or_known_package_fallback(): void
    {
        $saved = new Product(['price' => 990, 'regular_price' => 1400, 'is_modal_product' => false]);
        $fallback = new Product(['price' => 1250, 'is_modal_product' => false]);
        $modal = new Product(['price' => 1250, 'is_modal_product' => true]);

        $this->assertSame(1400, $saved->displayRegularPrice());
        $this->assertSame(1650, $fallback->displayRegularPrice());
        $this->assertNull($modal->displayRegularPrice());
    }

    public function test_it_uses_the_fallback_when_the_uploaded_product_image_is_missing(): void
    {
        Storage::fake('public');
        $product = new Product([
            'image_path' => 'products/missing.jpg',
            'fallback_image' => 'asset/images/furniture-polish-combo.png',
        ]);

        $this->assertSame(asset('asset/images/furniture-polish-combo.png'), $product->imageUrl());
    }

    public function test_it_uses_the_media_route_when_the_product_image_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/available.jpg', 'image');
        $product = new Product(['image_path' => 'products/available.jpg']);

        $this->assertSame(route('media.show', ['path' => 'products/available.jpg']), $product->imageUrl());
    }
}
