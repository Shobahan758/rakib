<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class LandingImage
{
    public static function url(string $path): string
    {
        $fallback = route('media.show', ['path' => $path]);
        if (! function_exists('imagewebp') || ! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $fallback;
        }

        $source = Storage::disk('public')->path($path);
        $relative = 'asset/optimized/'.hash('sha256', $path.'|'.filemtime($source).'|'.filesize($source).'|v1').'.webp';
        $target = public_path($relative);
        if (is_file($target)) {
            return asset($relative);
        }

        $info = @getimagesize($source);
        // Bound decoder memory; keep the original when GD cannot safely process it.
        if (! $info || $info[0] * $info[1] > 16000000) {
            return $fallback;
        }
        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
            default => false,
        };
        if (! $image) {
            return $fallback;
        }
        $width = min(1360, imagesx($image));
        $height = max(1, (int) round(imagesy($image) * $width / imagesx($image)));
        $output = imagecreatetruecolor($width, $height);
        imagealphablending($output, false);
        imagesavealpha($output, true);
        imagecopyresampled($output, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
        if (! is_dir(dirname($target))) {
            @mkdir(dirname($target), 0755, true);
        }
        $temp = @tempnam(dirname($target), 'image-');
        $saved = $temp && @imagewebp($output, $temp, 78) && @rename($temp, $target);
        if ($temp && is_file($temp)) {
            @unlink($temp);
        }
        imagedestroy($image);
        imagedestroy($output);

        return $saved ? asset($relative) : $fallback;
    }
}
