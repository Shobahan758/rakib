<?php

namespace Tests\Feature;

use App\Models\LandingSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_supported_links_save_and_render_ahead_of_an_uploaded_video(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        LandingSection::create(['slug' => 'video', 'content' => ['video_file' => 'landing/videos/old.mp4'], 'is_visible' => true]);
        $embed = 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0';
        foreach ([
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => $embed,
            'https://youtu.be/dQw4w9WgXcQ?si=share' => $embed,
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => $embed,
            'https://www.youtube.com/live/dQw4w9WgXcQ' => $embed,
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ' => $embed,
            'https://cdn.example.com/demo.mp4?token=abc' => 'https://cdn.example.com/demo.mp4?token=abc',
            'https://cdn.example.com/demo.webm' => 'https://cdn.example.com/demo.webm',
            'https://cdn.example.com/demo.mov' => 'https://cdn.example.com/demo.mov',
        ] as $input => $output) {
            $this->put(route('admin.landing.update', 'video'), ['video_url' => $input, 'is_visible' => 1])
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame($input, LandingSection::where('slug', 'video')->first()->content['video_url']);
            $this->get(route('admin.landing.edit', 'video'))->assertOk()
                ->assertSee('সেভ করা ভিডিওর প্রিভিউ')->assertSee($output, false);
            $this->get(route('home'))->assertOk()->assertSee($output, false)->assertDontSee('/media/landing/videos/old.mp4', false);
        }
        $this->put(route('admin.landing.update', 'video'), ['video_url' => '', 'is_visible' => 1])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertOk()->assertSee(route('media.show', ['path' => 'landing/videos/old.mp4']), false);
    }

    public function test_unsupported_or_unsafe_urls_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        foreach (['javascript:alert(1)', 'https://youtube.com.evil.test/watch?v=dQw4w9WgXcQ', 'https://example.com/page', 'https://youtube.com/watch?v[]=invalid'] as $url) {
            $this->put(route('admin.landing.update', 'video'), ['video_url' => $url])->assertSessionHasErrors('video_url');
        }
    }
}
