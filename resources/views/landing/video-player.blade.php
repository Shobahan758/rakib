@php
    $shouldAutoplay = (bool) ($autoplay ?? false);
    $playerUrl = $source['url'];
    if ($source['type'] === 'embed' && $shouldAutoplay) {
        $playerUrl .= (str_contains($playerUrl, '?') ? '&' : '?').'autoplay=1&mute=1&playsinline=1';
    }
@endphp
@if ($source['type'] === 'embed')
    <iframe src="{{ $playerUrl }}" title="{{ $title }}" loading="{{ $shouldAutoplay ? 'eager' : 'lazy' }}"
        referrerpolicy="strict-origin-when-cross-origin"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen></iframe>
@else
    <video controls playsinline preload="{{ $shouldAutoplay ? 'auto' : 'metadata' }}"
        @if($shouldAutoplay) autoplay muted @endif @if (!empty($poster)) poster="{{ $poster }}" @endif
        aria-label="{{ $title }}">
        <source src="{{ $source['url'] }}">
        <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">{{ $fallbackText ?? 'ভিডিওটি দেখতে এই লিংক খুলুন।' }}</a>
    </video>
@endif
