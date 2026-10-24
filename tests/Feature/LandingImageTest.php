<?php

namespace Tests\Feature;

use App\Support\LandingImage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingImageTest extends TestCase
{
    public function test_uploaded_images_are_resized_and_cached_as_static_webp(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support required.');
        }
        Storage::fake('public');
        $image = imagecreatetruecolor(2000, 1000);
        $path = Storage::disk('public')->path('hero.png');
        imagepng($image, $path);
        imagedestroy($image);
        $url = LandingImage::url('hero.png');
        $target = public_path(parse_url($url, PHP_URL_PATH));
        try {
            $this->assertStringContainsString('/asset/optimized/', $url);
            $this->assertSame([1360, 680], array_slice(getimagesize($target), 0, 2));
            $this->assertSame($url, LandingImage::url('hero.png'));
            $this->assertFileExists($path);
            $this->assertSame(0644, fileperms($target) & 0777);
        } finally {
            @unlink($target);
        }
    }
}
