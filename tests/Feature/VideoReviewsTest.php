<?php

namespace Tests\Feature;

use App\Models\LandingSection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
