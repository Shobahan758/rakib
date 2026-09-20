<?php

namespace Tests\Feature;

use App\Models\LandingSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteCustomizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
    }

    public function test_theme_branding_visibility_and_slider_options_are_editable(): void
    {
        $this->put(route('admin.landing.update', 'site'), [
            'site_name' => 'Custom Furniture Store', 'primary_color' => '#123abc',
            'base_font_size' => 18, 'mobile_font_size' => 16,
            'footer_visible' => '0', 'mobile_order_visible' => '0',
        ])->assertSessionHasNoErrors();
        $this->put(route('admin.landing.update', 'social'), ['whatsapp_visible' => '0'])->assertSessionHasNoErrors();
        $this->put(route('admin.landing.update', 'video_reviews'), [
            'is_visible' => 1, 'slider_autoplay' => '0', 'slider_interval' => 8,
            'slider_pause_label' => 'Pause reviews', 'slider_resume_label' => 'Resume reviews',
            'video_links' => "https://youtu.be/1kvRiZnBQqA\nhttps://youtu.be/rBF18mklgaE",
        ])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertOk()->assertSee('--orange:#123abc;', false)
            ->assertSee('font-size:18px;', false)->assertSee('data-interval="8"', false)
            ->assertSee('data-autoplay="0"', false)->assertSee('Resume reviews')
            ->assertDontSee('<footer>', false)->assertDontSee('class="mobile-order-bar', false)
            ->assertDontSee('class="floating-actions"', false);
        $this->get(route('admin.landing.edit', 'site'))->assertOk()->assertSee('Custom Furniture Store');
    }

    public function test_section_mobile_image_colors_and_typography_save_and_render(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->createWithContent('mobile.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        $this->put(route('admin.landing.update', 'hero'), [
            'is_visible' => 1, 'mobile_background_image' => $image,
            'section_heading_color' => '#112233', 'section_heading_size' => 52,
            'section_mobile_heading_size' => 28, 'section_background_position' => 'bottom',
            'section_background_size' => 'contain', 'button_link' => '#Order',
        ])->assertSessionHasNoErrors();
        $settings = LandingSection::where('slug', 'hero')->first()->content;
        Storage::disk('public')->assertExists($settings['mobile_background_image']);
        $this->get(route('home'))->assertOk()->assertSee('font-size:28px!important;', false)
            ->assertSee('color:#112233!important;', false)->assertSee('background-position:bottom!important;', false)
            ->assertSee(route('media.show', ['path' => $settings['mobile_background_image']]), false);
        $this->put(route('admin.landing.update', 'hero'), ['remove_mobile_background_image' => 1, 'is_visible' => 1])->assertSessionHasNoErrors();
        $this->assertArrayNotHasKey('mobile_background_image', LandingSection::where('slug', 'hero')->first()->content);
    }

    public function test_unsafe_values_and_extreme_sizes_are_rejected(): void
    {
        $this->put(route('admin.landing.update', 'hero'), [
            'section_text_color' => '</style><script>', 'section_mobile_heading_size' => 200,
            'section_background_position' => 'invalid', 'button_link' => 'javascript:alert(1)',
        ])->assertSessionHasErrors(['section_text_color', 'section_mobile_heading_size', 'section_background_position', 'button_link']);
        $this->put(route('admin.landing.update', 'video_reviews'), ['slider_interval' => 0])->assertSessionHasErrors('slider_interval');
    }

    public function test_extended_design_controls_save_and_render_on_desktop_and_mobile(): void
    {
        $this->put(route('admin.landing.update', 'hero'), [
            'is_visible' => 1, 'section_text_align' => 'left', 'section_line_height' => 1.9,
            'section_text_color' => '#112233', 'section_mobile_text_size' => 20,
            'section_button_color' => '#123456', 'section_button_text_color' => '#ffffff',
            'section_button_radius' => 24, 'section_image_width' => 480,
            'section_image_height' => 420, 'section_mobile_image_height' => 260,
            'section_image_fit' => 'contain', 'section_image_radius' => 16,
            'readable_background_color' => '#eeeeee',
        ])->assertSessionHasNoErrors();
        $html = $this->get(route('home'))->assertOk()->assertSee("font-family:'Hind Siliguri', sans-serif", false);
        foreach (['text-align:left!important', 'line-height:1.9!important', 'font-size:20px!important',
            'background:#123456!important', 'border-radius:24px!important', 'max-width:480px!important',
            'height:420px!important', 'height:260px!important', 'object-fit:contain!important',
            'background:#eeeeee!important'] as $css) $html->assertSee($css, false);
        $this->get(route('admin.landing.edit', 'hero'))->assertOk()->assertSee('name="section_mobile_image_height"', false);
        $this->put(route('admin.landing.update', 'hero'), ['is_visible' => 1, 'section_image_height' => '', 'readable_background_visible' => '0'])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertOk()->assertDontSee('height:420px!important', false)->assertSee('background:transparent!important', false);
    }

    public function test_global_buttons_footer_and_gallery_controls_are_connected(): void
    {
        $this->put(route('admin.landing.update', 'site'), [
            'font_family' => 'system', 'button_hover_enabled' => '0', 'button_radius' => 25,
            'button_text_color' => '#112233', 'footer_background_color' => '#101010',
            'footer_text_color' => '#eeeeee', 'mobile_order_link' => '#home',
        ])->assertSessionHasNoErrors();
        $this->put(route('admin.landing.update', 'gallery'), [
            'is_visible' => 1, 'slider_autoplay' => '0', 'slider_interval' => 9,
            'slider_pause_label' => 'Pause gallery', 'slider_resume_label' => 'Resume gallery',
        ])->assertSessionHasNoErrors();
        $this->put(route('admin.landing.update', 'menu'), ['is_visible' => 1, 'button_link' => '#home'])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertOk()->assertSee('font-family:system-ui, sans-serif', false)
            ->assertSee('border-radius:25px!important', false)->assertSee('background-color:#101010!important', false)
            ->assertSee('transform:none!important;filter:none!important', false)
            ->assertSee('data-interval="9"', false)->assertSee('data-resume-label="Resume gallery"', false)
            ->assertSee('href="#home" class="btn btn-order w-100"', false)
            ->assertSee('href="#home" class="btn btn-order py-2 px-3"', false);
        $this->put(route('admin.landing.update', 'site'), ['mobile_order_link' => 'javascript:alert(1)', 'font_family' => 'bad', 'button_radius' => 101])
            ->assertSessionHasErrors(['mobile_order_link', 'font_family', 'button_radius']);
        $this->put(route('admin.landing.update', 'hero'), ['section_line_height' => 4, 'section_image_fit' => 'bad', 'section_button_color' => 'red'])
            ->assertSessionHasErrors(['section_line_height', 'section_image_fit', 'section_button_color']);
    }

    public function test_order_section_uses_uploaded_image_instead_of_the_first_product(): void
    {
        Storage::fake('public');
        $this->put(route('admin.landing.update', 'order'), [
            'is_visible' => 1, 'image' => UploadedFile::fake()->image('order.png'), 'image_alt' => 'Custom order image',
        ])->assertSessionHasNoErrors();
        $path = LandingSection::where('slug', 'order')->first()->content['image'];
        $this->get(route('home'))->assertOk()->assertSee('data-custom-image="1"', false)
            ->assertSee(route('media.show', ['path' => $path]), false)->assertSee('alt="Custom order image"', false);
    }
}
