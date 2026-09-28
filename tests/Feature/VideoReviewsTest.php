<?php

namespace Tests\Feature;

use App\Models\LandingSection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoReviewsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_video_review_links_can_be_saved_rendered_and_removed(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $links = "https://www.youtube.com/shorts/1kvRiZnBQqA\nhttps://youtu.be/rBF18mklgaE";
        $this->put(route('admin.landing.update', 'video_reviews'), [
            'video_links' => $links, 'title' => 'Customer video reviews', 'is_visible' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($links, LandingSection::where('slug', 'video_reviews')->first()->content['video_links']);
        $html = $this->get(route('home'))->assertOk()->assertSee('Customer video reviews')->getContent();
        $this->assertSame(2, substr_count($html, 'class="video-review-card"'));
        $this->assertLessThan(strpos($html, 'data-section="video_reviews"'), strpos($html, 'data-section="reviews"'));
        $this->get(route('admin.landing.edit', 'video_reviews'))->assertOk()->assertSee('1kvRiZnBQqA');
        $this->put(route('admin.landing.update', 'video_reviews'), ['video_links' => 'https://example.com/page'])->assertSessionHasErrors('video_links');
        $this->put(route('admin.landing.update', 'video_reviews'), ['video_links' => '', 'is_visible' => 1])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertOk()->assertDontSee('class="section-pad video-reviews-section"', false);
    }

    public function test_each_video_review_can_have_a_custom_poster(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        LandingSection::create([
            'slug' => 'video_reviews',
            'content' => ['video_links' => "https://youtu.be/1kvRiZnBQqA\nhttps://youtu.be/rBF18mklgaE"],
            'is_visible' => true,
        ]);

        $this->get(route('admin.landing.edit', 'video_reviews'))
            ->assertOk()
            ->assertSee('ভিডিও 1-এর ব্যানার')
            ->assertSee('ভিডিও 2-এর ব্যানার');

        $this->put(route('admin.landing.update', 'video_reviews'), [
            'video_links' => "https://youtu.be/1kvRiZnBQqA\nhttps://youtu.be/rBF18mklgaE",
            'video_poster_1' => UploadedFile::fake()->image('poster.webp', 900, 1600),
            'is_visible' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $section = LandingSection::where('slug', 'video_reviews')->firstOrFail();
        Storage::disk('public')->assertExists($section->content['video_poster_1']);
        $this->get(route('home'))->assertOk()
            ->assertSee(route('media.show', ['path' => $section->content['video_poster_1']]), false);

        $this->put(route('admin.landing.update', 'video_reviews'), [
            'video_links' => $section->content['video_links'],
            'remove_video_poster_1' => 1,
            'is_visible' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($section->content['video_poster_1']);
        $this->assertArrayNotHasKey('video_poster_1', $section->fresh()->content);
    }
}
