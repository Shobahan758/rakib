<?php

namespace App\Support;

final class ImageUploadSpecification
{
    /** @return array{width: int, height: int, advice: string} */
    public static function for(string $slug, string $imageKey): array
    {
        [$width, $height, $advice] = match (true) {
            $imageKey === 'background_image' => [1920, 1080, 'Desktop background স্ক্রিন অনুযায়ী কিছুটা কাটতে পারে; গুরুত্বপূর্ণ লেখা ছবিতে রাখবেন না।'],
            $imageKey === 'mobile_background_image' => [750, 1334, 'Mobile background-এর গুরুত্বপূর্ণ অংশ ছবির মাঝখানে রাখুন।'],
            $imageKey === 'favicon' => [512, 512, 'বর্গাকার icon দিন; চারপাশে অল্প ফাঁকা জায়গা রাখুন।'],
            $imageKey === 'modal_image' => [800, 400, 'Order confirmation popup-এ সম্পূর্ণ ছবিটি দেখাবে।'],
            str_starts_with($imageKey, 'review_image_') => [900, 1200, 'Review screenshot-এর লেখা পরিষ্কার রাখুন; সম্পূর্ণ ছবিটি frontend-এ দেখা যাবে।'],
            $slug === 'site' => [400, 160, 'Logo-এর চারপাশে অতিরিক্ত ফাঁকা জায়গা রাখবেন না।'],
            $slug === 'seo' => [1200, 630, 'Facebook ও অন্যান্য social share preview-এর standard ratio।'],
            $slug === 'video' => [1280, 720, 'Video poster-এর জন্য 16:9 ratio রাখুন।'],
            $slug === 'features' => [800, 600, 'সব feature image-এ একই 4:3 ratio রাখুন।'],
            $slug === 'reviews' => [400, 400, 'Customer avatar-এর মুখ ছবির মাঝখানে রাখুন।'],
            $slug === 'deal' => [1200, 800, 'Offer image-এর পণ্যটি ছবির মাঝখানে রাখুন।'],
            $slug === 'hero' => [1358, 798, 'Hero slider-এর 1358:798 ratio রাখুন; গুরুত্বপূর্ণ অংশ কিনারা থেকে একটু ভেতরে রাখুন।'],
            $slug === 'story' => [1200, 1200, 'Story section-এর জন্য বর্গাকার ছবি দিন; সম্পূর্ণ পণ্যটি দৃশ্যমান রাখুন।'],
            $slug === 'package_comparison' => [1200, 1200, 'Comparison section-এর জন্য বর্গাকার ছবি দিন; সম্পূর্ণ প্যাকেজটি দৃশ্যমান রাখুন।'],
            $slug === 'menu' => [1000, 1000, 'Menu card-এর জন্য বর্গাকার product image দিন।'],
            $slug === 'gallery' => [1000, 1000, 'Gallery card-এর জন্য বর্গাকার ছবি দিন।'],
            $slug === 'order' => [1000, 1000, 'Order form-এর জন্য বর্গাকার product image দিন।'],
            $slug === 'product' => [1200, 1200, 'Product card ও recommendation popup-এর জন্য বর্গাকার ছবি দিন।'],
            default => [1000, 1000, 'বর্গাকার ছবি দিন; মূল বিষয়টি ছবির মাঝখানে রাখুন।'],
        };

        return compact('width', 'height', 'advice');
    }
}
