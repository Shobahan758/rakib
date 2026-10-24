<?php

namespace Tests\Feature;

use App\Models\LandingSection;
use App\Models\Product;
use App\Models\TrackingSetting;
use App\Support\LandingPageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LandingPageDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_warm_page_data_needs_no_queries_and_settings_changes_invalidate_it(): void
    {
        $section = LandingSection::updateOrCreate(['slug' => 'hero'], [ 'content' => ['title' => 'Before'], 'is_visible' => true]);
        LandingPageData::get();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertSame('Before', LandingPageData::get()['sections']['hero']->content['title']);
        $this->assertCount(0, DB::getQueryLog());
        $section->update(['content' => ['title' => 'After']]);
        $this->assertSame('After', LandingPageData::get()['sections']['hero']->content['title']);
        $section->delete();
        $this->assertFalse(LandingPageData::get()['sections']->has('hero'));
        DB::disableQueryLog();
    }

    public function test_production_home_renders_with_file_cache(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        LandingPageData::forget();
        try {
            $this->get('/')->assertOk();
            $this->get('/')->assertOk();
        } finally {
            LandingPageData::forget();
            $this->app->detectEnvironment(fn () => 'testing');
        }
    }

    public function test_product_and_tracking_edits_clear_cached_data(): void
    {
        LandingPageData::get();
        $product = Product::create(['name' => 'Cache test product', 'price' => 990, 'is_active' => true]);
        $this->assertTrue(LandingPageData::get()['products']->contains('id', $product->id));
        $product->update(['is_active' => false]);
        $this->assertFalse(LandingPageData::get()['products']->contains('id', $product->id));
        $setting = TrackingSetting::create(['key' => 'google_analytics_id', 'value' => 'G-FIRST']);
        $this->assertSame('G-FIRST', LandingPageData::get()['tracking']['google_analytics_id']);
        $setting->update(['value' => 'G-SECOND']);
        $this->assertSame('G-SECOND', LandingPageData::get()['tracking']['google_analytics_id']);
    }
}
