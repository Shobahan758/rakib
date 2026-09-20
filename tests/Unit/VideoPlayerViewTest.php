<?php

namespace Tests\Unit;

use Tests\TestCase;

class VideoPlayerViewTest extends TestCase
{
    public function test_embed_autoplay_is_muted_and_eagerly_loaded(): void
    {
        $html = view('landing.video-player', [
            'source' => ['type' => 'embed', 'url' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0'],
            'title' => 'Main video',
            'autoplay' => true,
        ])->render();

        $this->assertStringContainsString('autoplay=1&amp;mute=1&amp;playsinline=1', $html);
        $this->assertStringContainsString('loading="eager"', $html);
    }

    public function test_direct_video_autoplay_uses_browser_safe_attributes(): void
    {
        $html = view('landing.video-player', [
            'source' => ['type' => 'file', 'url' => 'https://example.test/video.mp4'],
            'title' => 'Main video',
            'poster' => null,
            'autoplay' => true,
        ])->render();

        $this->assertStringContainsString('autoplay muted', $html);
        $this->assertStringContainsString('playsinline', $html);
        $this->assertStringContainsString('preload="auto"', $html);
    }

    public function test_review_video_does_not_autoplay_by_default(): void
    {
        $html = view('landing.video-player', [
            'source' => ['type' => 'embed', 'url' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0'],
            'title' => 'Review video',
        ])->render();

        $this->assertStringNotContainsString('autoplay=1', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
    }
}
