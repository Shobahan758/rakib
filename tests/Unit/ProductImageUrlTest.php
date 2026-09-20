<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUrlTest extends TestCase
{
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
