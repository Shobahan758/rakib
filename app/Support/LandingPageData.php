<?php

namespace App\Support;

use App\Models\LandingSection;
use App\Models\Product;
use App\Models\TrackingSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LandingPageData
{
    public const CACHE_KEY = 'landing.page-data.v1';

    public static function get(): array
    {
        return Cache::store(app()->environment('testing') ? 'array' : 'file')->remember(self::CACHE_KEY, 300, function () {
            return [
                'sections' => LandingSection::all()->keyBy('slug'),
                'products' => Schema::hasTable('products')
                    ? Product::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
                    : collect(),
                'tracking' => TrackingSetting::activeValues(),
            ];
        });
    }

    public static function forget(): void
    {
        Cache::store(app()->environment('testing') ? 'array' : 'file')->forget(self::CACHE_KEY);
    }
}
