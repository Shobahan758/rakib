<?php

namespace Tests\Feature;

use App\Models\TrackingSetting;
use App\Models\TrackingEvent;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_meta_id_alone_enables_pixel_and_replacing_it_updates_the_landing_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $url = route('admin.tracking.update', 'meta');
        $this->put($url, ['meta_pixel_id' => ' ১২৩৪৫৬৭৮৯০১২৩৪৫ '])->assertSessionHasNoErrors();
        $this->assertSame('123456789012345', TrackingSetting::activeValues()['meta_pixel_id']);
        $this->get(route('home'))->assertOk()
            ->assertSee('https://connect.facebook.net/en_US/fbevents.js', false)
            ->assertSee('fbq(\'init\',"123456789012345")', false)
            ->assertSee("fbq('track','PageView')", false)
            ->assertSee('https://www.facebook.com/tr?id=123456789012345', false);
        $this->put($url, ['meta_pixel_id' => '987654321098765'])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertOk()->assertSee('987654321098765')->assertDontSee('123456789012345');
        $this->put($url, ['enabled' => 0, 'meta_pixel_id' => '987654321098765'])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertOk()->assertDontSee('fbevents.js')->assertDontSee('987654321098765');
    }

    public function test_invalid_meta_settings_do_not_replace_the_saved_pixel(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $url = route('admin.tracking.update', 'meta');
        $this->put($url, ['meta_pixel_id' => '123456789012345'])->assertSessionHasNoErrors();
        foreach (['', '000000000000000', '<script>fbq()</script>', ['123456789']] as $id) {
            $this->put($url, ['enabled' => 1, 'meta_pixel_id' => $id])->assertSessionHasErrors('meta_pixel_id');
            $this->assertSame('123456789012345', TrackingSetting::activeValues()['meta_pixel_id']);
        }
        $this->put($url, ['enabled' => 0, 'meta_pixel_id' => ''])->assertSessionHasNoErrors();
    }

    public function test_demo_id_is_labeled_and_never_replaces_a_real_pixel(): void
    {
        TrackingSetting::updateOrCreate(['key' => 'meta_pixel_id'], ['value' => null]);
        $migration = require database_path('migrations/2026_09_07_000004_add_demo_meta_pixel_setting.php');
        $migration->up();
        $this->assertSame(TrackingSetting::DEMO_META_PIXEL_ID, TrackingSetting::values()['meta_pixel_id']);
        $this->assertArrayNotHasKey('meta_pixel_id', TrackingSetting::activeValues());
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get(route('admin.tracking.edit', 'meta'))->assertOk()->assertSee('Demo ID');
        TrackingSetting::where('key', 'meta_pixel_id')->update(['value' => '123456789012345']);
        $migration->up();
        $this->assertSame('123456789012345', TrackingSetting::values()['meta_pixel_id']);
    }

    public function test_provider_settings_validate_save_render_and_disable(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        foreach (['meta' => ['meta_pixel_id' => '123456789012345'],
            'google' => ['google_analytics_id' => 'G-TEST12345', 'google_ads_id' => 'AW-123456789', 'google_ads_conversion_label' => 'Abc_Test123'],
            'tiktok' => ['tiktok_pixel_id' => 'TEST1234567890ABC'],
            'other' => ['clarity_id' => 'test123abc', 'custom_body_script' => '<script>window.testTrackerLoaded=true;</script>']] as $provider => $values) {
            $this->get(route('admin.tracking.edit', $provider))->assertOk();
            $this->put(route('admin.tracking.update', $provider), ['enabled' => 1, ...$values])->assertSessionHasNoErrors();
            $html = $this->get(route('home'))->assertOk();
            foreach ($values as $value) $html->assertSee($value, false);
            $this->put(route('admin.tracking.update', $provider), ['enabled' => 0, ...$values])->assertSessionHasNoErrors();
            $html = $this->get(route('home'))->assertOk();
            foreach ($values as $value) $html->assertDontSee($value, false);
        }
        $this->put(route('admin.tracking.update', 'google'), ['google_analytics_id' => '<script>bad</script>'])->assertSessionHasErrors('google_analytics_id');
    }

    public function test_ads_only_uses_ads_id_for_google_loader(): void
    {
        foreach (['google_enabled' => '1', 'google_analytics_id' => '', 'google_ads_id' => 'AW-987654321'] as $key => $value) {
            TrackingSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        $this->get(route('home'))->assertOk()->assertSee('gtag/js?id=AW-987654321', false);
    }

    public function test_visitors_and_browser_activity_are_recorded_but_purchase_cannot_be_forged(): void
    {
        $agent = 'Tracking test '.bin2hex(random_bytes(6));
        $this->withHeader('User-Agent', $agent)->get(route('home'))->assertOk();
        $this->get(route('home'))->assertOk();
        $hash = hash('sha256', '127.0.0.1|'.$agent);
        $this->assertSame(1, SiteVisit::where('visitor_hash', $hash)->whereDate('visited_on', today())->count());
        foreach (['page_view', 'checkout_view', 'order_button_click', 'form_start', 'whatsapp_click'] as $event) {
            $this->postJson(route('tracking.events'), ['event' => $event, 'path' => '/'])->assertCreated();
            $this->assertDatabaseHas('tracking_events', ['visitor_hash' => $hash, 'event_name' => $event]);
        }
        $this->postJson(route('tracking.events'), ['event' => 'order_completed'])->assertUnprocessable();
        $this->postJson(route('tracking.events'), ['event' => 'page_view', 'path' => ['invalid']])->assertUnprocessable();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get(route('admin.tracking.edit', ['provider' => 'visitors', 'period' => 'today']))->assertOk()
            ->assertSee('Refresh report')->assertViewHas('eventCounts', fn ($counts) => $counts['form_start'] >= 1);
    }

    public function test_live_report_returns_updated_counts_after_each_click(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $url = route('admin.tracking.edit', ['provider' => 'visitors', 'period' => 'today']);
        $before = (int) $this->getJson($url)->assertOk()->json('eventCounts.order_button_click');
        for ($i = 1; $i <= 2; $i++) {
            $this->post(route('tracking.events'), ['event' => 'order_button_click', 'path' => '/'])->assertCreated();
            $this->getJson($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private')
                ->assertJsonPath('eventCounts.order_button_click', $before + $i);
        }
        $this->get(route('admin.tracking.edit', 'visitors'))->assertOk()->assertSee('visitor-report.js');
        $this->get(route('home'))->assertOk()->assertSee('visitor-events.js');
    }

    public function test_tracking_configuration_requires_permission(): void
    {
        $this->get(route('admin.tracking.edit', 'meta'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'manager', 'permissions' => []]));
        $this->put(route('admin.tracking.update', 'meta'), ['meta_pixel_id' => '123456789'])->assertForbidden();
    }
}
