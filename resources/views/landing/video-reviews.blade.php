@php
    $videoReviews = collect(
        preg_split('/\R/u', trim((string) $content('video_reviews', 'video_links')), -1, PREG_SPLIT_NO_EMPTY),
    )
        ->map(
            fn($link) => ($source = \App\Support\VideoSource::fromUrl(trim($link)))
                ? $source + ['original_url' => trim($link)]
                : null,
        )
        ->filter()
        ->values();
@endphp
@if ($videoReviews->isNotEmpty())
    <section class="section-pad video-reviews-section" data-section="video_reviews" aria-labelledby="videoReviewsTitle">
        <div class="container">
            <div class="text-center mb-4">
                <span class="section-kicker mb-3">{{ $content('video_reviews', 'kicker') }}</span>
                <h2 class="section-title" id="videoReviewsTitle">{{ $content('video_reviews', 'title') }}</h2>
                <p class="section-copy">{{ $content('video_reviews', 'description') }}</p>
            </div>
            <div class="video-review-slider" data-autoplay="{{ $content('video_reviews', 'slider_autoplay') }}"
                data-interval="{{ $content('video_reviews', 'slider_interval') }}"
                data-pause-label="{{ $content('video_reviews', 'slider_pause_label') }}"
                data-resume-label="{{ $content('video_reviews', 'slider_resume_label') }}" role="region"
                aria-roledescription="carousel" aria-label="{{ $content('video_reviews', 'carousel_label') }}">
                <div class="video-review-track" data-video-count="{{ $videoReviews->count() }}" tabindex="0"
                    aria-label="{{ $content('video_reviews', 'track_label') }}">
                    @foreach ($videoReviews as $source)
                        <article class="video-review-card" role="group" aria-roledescription="slide"
                            aria-label="{{ $content('video_reviews', 'item_label') }} {{ $loop->iteration }} / {{ $loop->count }}">
                            <div class="video-review-player">
                                @include('landing.video-player', [
                                    'source' => $source,
                                    'title' => $content('video_reviews', 'video_label') . ' ' . $loop->iteration,
                                    'poster' => null,
                                    'playLabel' => $content('video_reviews', 'play_button_label'),
                                    'fallbackText' => $content('video_reviews', 'video_fallback_text'),
                                    'soundLabel' => $content('video_reviews', 'sound_button_label'),
                                    'soundEnabledLabel' => $content('video_reviews', 'sound_enabled_label'),
                                ])
                            </div>
                            <a class="video-review-link" href="{{ $source['original_url'] }}" target="_blank"
                                rel="noopener noreferrer">{{ $content('video_reviews', 'video_link_label') }}</a>
                        </article>
                    @endforeach
                </div>
                <div class="video-review-controls" hidden>
                    <button type="button" data-video-prev
                        aria-label="{{ $content('video_reviews', 'slider_prev_label') }}">←</button>
                    <button type="button" data-video-pause
                        aria-label="{{ $content('video_reviews', 'slider_pause_label') }}">{{ $content('video_reviews', 'slider_pause_label') }}</button>
                    <button type="button" data-video-next
                        aria-label="{{ $content('video_reviews', 'slider_next_label') }}">→</button>
                </div>
            </div>
        </div>
    </section>
    <script src="{{ asset('asset/js/video-reviews.js') }}?v={{ filemtime(public_path('asset/js/video-reviews.js')) }}"
        defer></script>
@endif
