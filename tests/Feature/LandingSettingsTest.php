<?php

namespace Tests\Feature;

use App\Models\LandingSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('settings.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    public function test_hero_accepts_unlimited_slider_images_and_renders_matching_slides(): void
    {
        Storage::fake('public');
        $url = route('admin.landing.update', 'hero');

        $this->put($url, [
            'is_visible' => 1,
            'image_3' => $this->image(),
            'image_17' => $this->image(),
            'image_3_alt' => 'Third uploaded hero slide',
            'image_17_alt' => 'Seventeenth uploaded hero slide',
        ])->assertSessionHasNoErrors();

        $saved = LandingSection::where('slug', 'hero')->firstOrFail()->content;
        Storage::disk('public')->assertExists([$saved['image_3'], $saved['image_17']]);
        $this->assertSame(
            ['image_1', 'image_2', 'image_3', 'image_17'],
            LandingSection::heroImageKeys($saved),
        );

        $html = $this->get(route('home'))->assertOk()
            ->assertSee('Third uploaded hero slide')
            ->assertSee('Seventeenth uploaded hero slide')
            ->getContent();
        $this->assertSame(5, substr_count($html, 'class="hero-slide"'));
        $this->assertSame(5, substr_count($html, 'aria-label="Hero ছবি '));
        $this->get(route('admin.landing.edit', 'hero'))->assertOk()
            ->assertSee('name="image_17"', false)
            ->assertSee('hero-upload-card', false)
            ->assertSee('প্রস্তাবিত মাপ: 1358 × 798 px')
            ->assertSee('আরও ছবি যোগ করুন');

        $this->put($url, ['is_visible' => 1, 'remove_image_17' => 1])->assertSessionHasNoErrors();
        $updated = LandingSection::where('slug', 'hero')->firstOrFail()->content;
        $this->assertNull($updated['image_17']);
        Storage::disk('public')->assertMissing($saved['image_17']);
        $this->get(route('home'))->assertOk()->assertDontSee('Seventeenth uploaded hero slide');
    }

    public function test_hero_default_slider_images_can_be_removed_and_uploaded_again(): void
    {
        Storage::fake('public');
        $url = route('admin.landing.update', 'hero');

        $this->put($url, ['is_visible' => 1, 'remove_image_1' => 1])->assertSessionHasNoErrors();
        $content = LandingSection::where('slug', 'hero')->firstOrFail()->content;
        $this->assertSame(['image_2'], LandingSection::heroImageKeys($content));
        $this->get(route('admin.landing.edit', 'hero'))->assertOk()->assertSee('name="image_1"', false);

        $this->put($url, [
            'is_visible' => 1,
            'image_1' => $this->image(),
            'image_1_alt' => 'Restored first hero slide',
        ])->assertSessionHasNoErrors();
        $content = LandingSection::where('slug', 'hero')->firstOrFail()->content;
        Storage::disk('public')->assertExists($content['image_1']);
        $this->assertSame(['image_1', 'image_2'], LandingSection::heroImageKeys($content));
        $this->get(route('home'))->assertOk()->assertSee('Restored first hero slide');
    }

    public function test_missing_hero_uploads_use_bundled_fallback_images(): void
    {
        Storage::fake('public');
        LandingSection::updateOrCreate(['slug' => 'hero'], [
            'content' => array_merge(LandingSection::defaults('hero'), [
                'image' => 'landing/missing-main.jpg',
                'image_1' => 'landing/missing-comparison.jpg',
                'image_2' => 'landing/missing-package.jpg',
            ]),
            'is_visible' => true,
        ]);

        $response = $this->get(route('home'))->assertOk();

        $response
            ->assertDontSee('/media/landing/missing-main.jpg', false)
            ->assertDontSee('/media/landing/missing-comparison.jpg', false)
            ->assertDontSee('/media/landing/missing-package.jpg', false)
            ->assertSee(asset('asset/images/hero-bed-comparison.png'), false)
            ->assertSee(asset('asset/images/furniture-polish-combo.png'), false);
    }

    public function test_gallery_accepts_additional_images_and_preserves_them_between_saves(): void
    {
        Storage::fake('public');
        $url = route('admin.landing.update', 'gallery');
        $this->put($url, [
            'is_visible' => 1, 'image_7' => $this->image(), 'image_20' => $this->image(),
            'image_7_alt' => 'Seventh gallery photo', 'image_20_alt' => 'Twentieth gallery photo',
        ])->assertSessionHasNoErrors();
        $saved = LandingSection::where('slug', 'gallery')->firstOrFail()->content;
        Storage::disk('public')->assertExists([$saved['image_7'], $saved['image_20']]);
        $this->assertSame(['image_7', 'image_20'], LandingSection::galleryImageKeys($saved));
        $html = $this->get(route('home'))->assertOk()->getContent();
        preg_match('/<section[^>]*data-section="gallery"(.*?)<\/section>/s', $html, $gallery);
        $this->assertSame(2, substr_count($gallery[1], 'class="review-slide"'));
        $this->assertStringNotContainsString('asset/images/burger', $gallery[1]);
        $this->get(route('home'))->assertOk()->assertSee('Seventh gallery photo')->assertSee('Twentieth gallery photo');
        $this->get(route('admin.landing.edit', 'gallery'))->assertOk()
            ->assertSee('name="image_20"', false)->assertSee('আরও ছবি যোগ করুন');
        $this->put($url, ['is_visible' => 1, 'title' => 'Updated gallery'])->assertSessionHasNoErrors();
        $this->assertSame($saved['image_20'], LandingSection::where('slug', 'gallery')->firstOrFail()->content['image_20']);
        $this->put($url, ['is_visible' => 1, 'remove_image_7' => 1, 'remove_image_1' => 1])->assertSessionHasNoErrors();
        $updated = LandingSection::where('slug', 'gallery')->firstOrFail()->content;
        $this->assertSame(['image_20'], LandingSection::galleryImageKeys($updated));
        $this->get(route('home'))->assertOk()->assertDontSee('Seventh gallery photo')->assertSee('Twentieth gallery photo');
        $this->get(route('admin.landing.edit', 'gallery'))->assertOk()->assertDontSee('name="image_7"', false);
    }

    public function test_gallery_only_uses_demo_images_when_no_uploads_exist(): void
    {
        $this->assertCount(6, LandingSection::galleryImageKeys([]));
        $this->assertSame(['image_2', 'image_3', 'image_4', 'image_5', 'image_6'], LandingSection::galleryImageKeys(['image_1' => null]));
        $this->assertSame([], LandingSection::galleryImageKeys(array_fill_keys(
            array_map(fn ($i) => "image_{$i}", range(1, 6)), null,
        )));
        $this->assertSame(['image_2', 'image_10'], LandingSection::galleryImageKeys([
            'image_10' => 'landing/ten.png', 'image_2' => 'landing/two.png', 'image_1_alt' => 'Description',
        ]));
    }

    public function test_gallery_validates_additional_uploads_and_alt_text(): void
    {
        Storage::fake('public');
        $this->put(route('admin.landing.update', 'gallery'), [
            'image_21' => UploadedFile::fake()->create('invalid.txt', 1, 'text/plain'),
            'image_22' => UploadedFile::fake()->image('large.png')->size(4097),
            'image_21_alt' => str_repeat('x', 1001),
        ])->assertSessionHasErrors(['image_21', 'image_22', 'image_21_alt']);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->get(route('admin.landing.edit', 'gallery'))->assertOk()->assertSee('name="image_21"', false);
    }

    public function test_review_screenshots_replace_text_and_can_be_removed(): void
    {
        LandingSection::updateOrCreate(['slug' => 'reviews'], ['content' => LandingSection::defaults('reviews'), 'is_visible' => true]);
        $editor = $this->get(route('admin.landing.edit', 'reviews'))->assertOk()->assertSee('রিভিউ ছবির স্লাইডার');
        foreach (range(1, 6) as $i) {
            $this->assertSame(1, substr_count($editor->getContent(), 'name="review_image_'.$i.'"'));
        }
        Storage::fake('public');
        $this->put(route('admin.landing.update', 'reviews'), [
            'is_visible' => 1, 'review_image_1' => $this->image(),
            'review_image_6' => $this->image(), 'review_1_text' => 'Text fallback review',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $settings = LandingSection::where('slug', 'reviews')->firstOrFail()->content;
        Storage::disk('public')->assertExists($settings['review_image_1']);
        $this->get(route('home'))->assertOk()
            ->assertSee(route('media.show', ['path' => $settings['review_image_6']]), false)
            ->assertSee('review-slider.js')->assertDontSee('Text fallback review');
        $this->put(route('admin.landing.update', 'reviews'), [
            'is_visible' => 1, 'remove_review_image_1' => 1, 'remove_review_image_6' => 1,
        ])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertOk()->assertSee('Text fallback review');
    }

    public function test_review_images_beyond_six_can_be_added_replaced_and_removed(): void
    {
        Storage::fake('public');
        $url = route('admin.landing.update', 'reviews');
        $this->put($url, ['is_visible' => 1, 'review_image_6' => $this->image(), 'review_image_7' => $this->image(), 'review_image_20' => $this->image()])->assertSessionHasNoErrors();
        $saved = LandingSection::where('slug', 'reviews')->firstOrFail()->content;
        $this->get(route('admin.landing.edit', 'reviews'))->assertOk()->assertSee('name="review_image_20"', false)->assertDontSee('সর্বোচ্চ ৬টি');
        $this->get(route('home'))->assertOk()->assertSee(route('media.show', ['path' => $saved['review_image_20']]), false);
        $this->put($url, ['is_visible' => 1, 'review_image_7' => $this->image(), 'remove_review_image_20' => 1])->assertSessionHasNoErrors();
        $updated = LandingSection::where('slug', 'reviews')->firstOrFail()->content;
        $this->assertSame($saved['review_image_6'], $updated['review_image_6']);
        $this->assertNotSame($saved['review_image_7'], $updated['review_image_7']);
        $this->assertArrayNotHasKey('review_image_20', $updated);
        $this->put($url, ['review_image_21' => UploadedFile::fake()->create('bad.txt', 1, 'text/plain')])->assertSessionHasErrors('review_image_21');
        $this->assertSame(['review_image_6', 'review_image_7'], LandingSection::reviewImageKeys($updated));
    }

    public function test_review_screenshot_rejects_non_image_uploads(): void
    {
        $this->put(route('admin.landing.update', 'reviews'), [
            'review_image_1' => UploadedFile::fake()->create('review.txt', 1, 'text/plain'),
        ])->assertSessionHasErrors('review_image_1');
    }

    public function test_large_review_images_are_allowed_in_existing_and_new_slots(): void
    {
        Storage::fake('public');
        $this->put(route('admin.landing.update', 'reviews'), [
            'is_visible' => 1,
            'review_image_4' => UploadedFile::fake()->image('large.png')->size(8192),
            'review_image_25' => UploadedFile::fake()->image('another.png')->size(16384),
        ])->assertSessionHasNoErrors();
        $content = LandingSection::where('slug', 'reviews')->firstOrFail()->content;
        Storage::disk('public')->assertExists($content['review_image_4']);
        Storage::disk('public')->assertExists($content['review_image_25']);
        $this->get(route('home'))->assertOk()->assertSee(route('media.show', ['path' => $content['review_image_25']]), false);
        $this->get(route('admin.landing.edit', 'reviews'))->assertOk()->assertSee('ফাইল সাইজে অ্যাপের নির্দিষ্ট সীমা নেই');
    }

    public function test_every_frontend_section_has_a_working_settings_editor(): void
    {
        foreach (LandingSection::definitions() as $slug => $definition) {
            $this->get(route('admin.landing.edit', $slug))->assertOk()->assertSee($definition['label']);
        }
        $this->get(route('admin.landing.index'))->assertOk()->assertSee('Edit content');
    }

    public function test_saved_text_images_and_responsive_dimensions_render_on_frontend(): void
    {
        Storage::fake('public');
        $this->put(route('admin.landing.update', 'hero'), [
            'is_visible' => 1, 'title' => 'Custom hero headline', 'description' => '',
            'section_min_height' => 600, 'section_mobile_min_height' => 400,
            'section_padding_top' => 0, 'section_mobile_padding_bottom' => 24,
            'image' => $this->image(), 'background_image' => $this->image(),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $settings = LandingSection::where('slug', 'hero')->firstOrFail()->content;
        Storage::disk('public')->assertExists($settings['image']);
        Storage::disk('public')->assertExists($settings['background_image']);
        $this->get(route('home'))->assertOk()->assertSee('Custom hero headline')
            ->assertDontSee(LandingSection::defaults('hero')['description'])
            ->assertSee(route('media.show', ['path' => $settings['image']]), false)
            ->assertSee('min-height:600px!important;', false)->assertSee('min-height:400px!important;', false)
            ->assertSee('padding-top:0px!important;', false)->assertSee('padding-bottom:24px!important;', false);
        $this->put(route('admin.landing.update', 'hero'), ['is_visible' => 1, 'remove_image' => 1, 'remove_background_image' => 1])
            ->assertSessionHasNoErrors();
        $settings = LandingSection::where('slug', 'hero')->firstOrFail()->content;
        $this->assertArrayNotHasKey('image', $settings);
        $this->assertArrayNotHasKey('background_image', $settings);
        $this->assertSame('Custom hero headline', $settings['title']);
    }

    public function test_previously_unused_fields_and_modal_copy_are_editable(): void
    {
        foreach (['features' => ['card_1_text' => 'Updated feature detail'], 'video' => ['description' => 'Updated video detail'],
            'order' => ['modal_title' => 'Updated success heading', 'products_title' => 'Choose products here']] as $slug => $values) {
            $this->put(route('admin.landing.update', $slug), ['is_visible' => 1, ...$values])->assertSessionHasNoErrors();
        }
        $this->get(route('home'))->assertOk()->assertSee('Updated feature detail')->assertSee('Updated video detail')
            ->assertSee('Updated success heading')->assertSee('Choose products here');
    }

    public function test_invalid_dimensions_are_rejected_and_visibility_is_preserved_after_validation_error(): void
    {
        $this->put(route('admin.landing.update', 'hero'), ['section_min_height' => -1, 'section_mobile_min_height' => 3001])
            ->assertSessionHasErrors(['section_min_height', 'section_mobile_min_height']);
        $this->put(route('admin.landing.update', 'hero'), ['title' => 'Hidden hero', 'is_visible' => 0])->assertSessionHasNoErrors();
        $this->assertFalse(LandingSection::where('slug', 'hero')->firstOrFail()->is_visible);
        $this->get(route('home'))->assertOk()->assertSee('display:none!important;', false);
        $this->put(route('admin.landing.update', 'hero'), ['is_visible' => 1])->assertSessionHasNoErrors();
        $this->assertTrue(LandingSection::where('slug', 'hero')->firstOrFail()->is_visible);
    }

    public function test_menu_images_and_text_can_be_updated_together(): void
    {
        Storage::fake('public');
        $this->put(route('admin.landing.update', 'menu'), ['is_visible' => 1, 'item_1_name' => 'New menu item', 'item_1_price' => 599, 'image_1' => $this->image()])
            ->assertSessionHasNoErrors();
        $settings = LandingSection::where('slug', 'menu')->firstOrFail()->content;
        $this->get(route('home'))->assertOk()->assertSee('New menu item')->assertSee('599')
            ->assertSee(route('media.show', ['path' => $settings['image_1']]), false);
    }
}
