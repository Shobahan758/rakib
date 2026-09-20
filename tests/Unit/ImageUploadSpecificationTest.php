<?php

namespace Tests\Unit;

use App\Support\ImageUploadSpecification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ImageUploadSpecificationTest extends TestCase
{
    public static function imageSpecifications(): array
    {
        return [
            'desktop background' => ['hero', 'background_image', 1920, 1080],
            'mobile background' => ['hero', 'mobile_background_image', 750, 1334],
            'logo' => ['site', 'image', 400, 160],
            'social sharing image' => ['seo', 'image', 1200, 630],
            'hero slide' => ['hero', 'image_1', 1358, 798],
            'feature image' => ['features', 'image_1', 800, 600],
            'review screenshot' => ['reviews', 'review_image_1', 900, 1200],
            'customer avatar' => ['reviews', 'image_1', 400, 400],
            'comparison image' => ['package_comparison', 'image', 1200, 1200],
            'product image' => ['product', 'image', 1200, 1200],
            'confirmation image' => ['order', 'modal_image', 800, 400],
        ];
    }

    #[DataProvider('imageSpecifications')]
    public function test_it_returns_the_expected_size(string $slug, string $key, int $width, int $height): void
    {
        $specification = ImageUploadSpecification::for($slug, $key);

        $this->assertSame($width, $specification['width']);
        $this->assertSame($height, $specification['height']);
        $this->assertNotSame('', $specification['advice']);
    }
}
