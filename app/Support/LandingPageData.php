<?php

namespace App\Support;

use App\Models\LandingSection;
use App\Models\Product;
use App\Models\TrackingSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LandingPageData
{
    public const CACHE_KEY = 'landing.page-data.v2';

    public static function get(): array
    {
        $data = Cache::store(app()->environment('testing') ? 'array' : 'file')->remember(self::CACHE_KEY, 300, function () {
            return [
                'sections' => LandingSection::all()->map(fn ($section) => $section->getAttributes())->all(),
                'products' => Schema::hasTable('products')
                    ? Product::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->map(fn ($product) => $product->getAttributes())->all()
                    : [],
                'tracking' => TrackingSetting::activeValues(),
            ];
        });

        return [
            'sections' => LandingSection::hydrate($data['sections'])->keyBy('slug'),
            'products' => Product::hydrate($data['products']),
            'tracking' => $data['tracking'],
        ];
    }

    public static function forget(): void
    {
        Cache::store(app()->environment('testing') ? 'array' : 'file')->forget(self::CACHE_KEY);
    }
}
