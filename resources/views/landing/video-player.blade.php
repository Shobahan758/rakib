@php
    $shouldAutoplay = (bool) ($autoplay ?? false);
    $playerUrl = $source['url'];
    if ($source['type'] === 'embed' && $shouldAutoplay) {
        $playerUrl .= (str_contains($playerUrl, '?') ? '&' : '?').'autoplay=1&mute=1&playsinline=1&enablejsapi=1';
    }
@endphp
<div class="autoplay-video" data-autoplay-video data-player-type="{{ $source['type'] }}"
    data-sound-enabled-label="{{ $soundEnabledLabel ?? 'সাউন্ড চালু হয়েছে' }}">
    @if ($source['type'] === 'embed')
        <iframe src="{{ $playerUrl }}" title="{{ $title }}" loading="{{ $shouldAutoplay ? 'eager' : 'lazy' }}"
            data-video-player referrerpolicy="strict-origin-when-cross-origin"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen></iframe>
    @else
        <video controls playsinline preload="{{ $shouldAutoplay ? 'auto' : 'metadata' }}" data-video-player
            @if($shouldAutoplay) autoplay muted @endif @if (!empty($poster)) poster="{{ $poster }}" @endif
            aria-label="{{ $title }}">
            <source src="{{ $source['url'] }}">
            <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">{{ $fallbackText ?? 'ভিডিওটি দেখতে এই লিংক খুলুন।' }}</a>
        </video>
    @endif

    @if ($shouldAutoplay)
        <button class="video-sound-toggle" type="button" data-video-sound aria-label="{{ $soundLabel ?? 'সাউন্ড চালু করুন' }}">
            <span aria-hidden="true">🔊</span> {{ $soundLabel ?? 'সাউন্ড চালু করুন' }}
        </button>
    @endif
</div>

@once
    <script src="{{ asset('asset/js/video-sound.js') }}?v={{ filemtime(public_path('asset/js/video-sound.js')) }}" defer></script>
@endonce
