<!doctype html>
<html lang="bn">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $orderContent['modal_title'] }} | {{ $siteContent['site_name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('asset/css/style.css') }}?v={{ filemtime(public_path('asset/css/style.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('asset/css/responsive.css') }}?v={{ filemtime(public_path('asset/css/responsive.css')) }}">
    @if (!empty($orderContent['background_image']) || !empty($orderContent['mobile_background_image']))
        <style>
            @if (!empty($orderContent['background_image']))
                .order-result-page { background-image: url("{{ route('media.show', ['path' => $orderContent['background_image']]) }}"); }
            @endif
            @if (!empty($orderContent['mobile_background_image']))
                @media (max-width: 767px) {
                    .order-result-page { background-image: url("{{ route('media.show', ['path' => $orderContent['mobile_background_image']]) }}"); }
                }
            @endif
        </style>
    @endif
</head>

@php
    $validColor = fn ($key) => preg_match('/^#[a-fA-F0-9]{6}$/', $orderContent[$key] ?? '');
@endphp
<body class="order-result-page"
    style="--orange:{{ $siteContent['primary_color'] }};--orange-dark:{{ $siteContent['primary_dark_color'] }};--ink:{{ $siteContent['text_color'] }};--cream:{{ $siteContent['surface_color'] }};@if($validColor('section_background_color'))--result-page-bg:{{ $orderContent['section_background_color'] }};@endif @if($validColor('section_card_color'))--result-card-bg:{{ $orderContent['section_card_color'] }};@endif @if($validColor('section_card_text_color'))--result-card-text:{{ $orderContent['section_card_text_color'] }};@endif @if($validColor('section_heading_color'))--result-heading:{{ $orderContent['section_heading_color'] }};@endif @if($validColor('section_text_color'))--result-copy:{{ $orderContent['section_text_color'] }};@endif @if($validColor('section_button_color'))--result-button-bg:{{ $orderContent['section_button_color'] }};@endif @if($validColor('section_button_text_color'))--result-button-text:{{ $orderContent['section_button_text_color'] }};@endif @if(is_numeric($orderContent['section_button_radius'] ?? null))--result-button-radius:{{ max(0, min(100, (int) $orderContent['section_button_radius'])) }}px;@endif @if(is_numeric($orderContent['section_heading_size'] ?? null))--result-heading-size:{{ max(16, min(120, (int) $orderContent['section_heading_size'])) }}px;@endif @if(is_numeric($orderContent['section_mobile_heading_size'] ?? null))--result-mobile-heading-size:{{ max(16, min(120, (int) $orderContent['section_mobile_heading_size'])) }}px;@endif @if(is_numeric($orderContent['section_text_size'] ?? null))--result-copy-size:{{ max(12, min(32, (int) $orderContent['section_text_size'])) }}px;@endif @if(is_numeric($orderContent['section_mobile_text_size'] ?? null))--result-mobile-copy-size:{{ max(12, min(32, (int) $orderContent['section_mobile_text_size'])) }}px;@endif">
    <main class="order-result-shell">
        <section class="order-result-card" aria-labelledby="orderResultTitle">
            @if (!empty($orderContent['modal_image']))
                <img class="order-result-custom-image"
                    src="{{ route('media.show', ['path' => $orderContent['modal_image']]) }}"
                    alt="{{ $orderContent['modal_image_alt'] }}">
            @else
                <div class="order-result-icon" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
            @endif
            <h1 id="orderResultTitle">{{ $orderContent['modal_title'] }}</h1>
            <p class="order-result-description">{{ $orderContent['modal_description'] }}</p>

            <article class="order-result-product">
                <img src="{{ $addon->product?->imageUrl() }}" alt="{{ $addon->product?->name ?? $addon->burger_type }}"
                    width="220" height="180">
                <div>
                    <span class="order-result-label">{{ $orderContent['modal_success_product_label'] }}</span>
                    <h2>{{ $addon->product?->name ?? $addon->burger_type }}</h2>
                    <p>{{ $orderContent['modal_quantity_label'] }}: <strong>{{ $addon->quantity }}</strong></p>
                    <p>{{ $orderContent['modal_price_label'] }}: <strong>{{ $siteContent['currency_symbol'] }}{{ number_format($addon->total) }}</strong></p>
                </div>
            </article>

            <div class="order-result-summary">
                <strong>{{ $orderContent['modal_total_label'] }}
                    {{ $siteContent['currency_symbol'] }}{{ number_format($deliveryTotal) }}</strong>
            </div>

            @if ($orderContent['modal_reminder'])
                <p class="order-result-reminder">{{ $orderContent['modal_reminder'] }}</p>
            @endif
            @php
                $phone = $orderContent['modal_phone'] ?: $siteContent['phone'];
                $phoneLink = $orderContent['modal_phone_link'] ?: ($orderContent['modal_phone'] ?: $siteContent['phone_link']);
            @endphp
            <div class="order-result-actions">
                <a class="order-result-call" href="tel:{{ $phoneLink }}"><i class="bi bi-telephone-fill"></i>
                    {{ $phone }}</a>
                <a class="order-result-home" href="{{ route('home') }}">{{ $orderContent['modal_home_button'] }}</a>
            </div>
        </section>
    </main>
</body>

</html>
