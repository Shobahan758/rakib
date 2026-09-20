<!doctype html>
<html lang="bn">

<head>
    @php
        $content = fn(string $section, string $key) => array_key_exists($key, $sections->get($section)?->content ?? [])
            ? data_get($sections->get($section)->content, $key)
            : data_get(\App\Models\LandingSection::defaults($section), $key);
        $storedMediaExists = fn(mixed $path): bool => is_string($path)
            && $path !== ''
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
        $sectionImage = function (string $section, string $fallback, string $key = 'image') use ($sections, $storedMediaExists): string {
            $path = data_get($sections->get($section)?->content, $key);

            if ($storedMediaExists($path)) {
                return route('media.show', ['path' => $path]);
            }

            return $fallback !== '' ? asset($fallback) : '';
        };
        $configuredCanonical = trim((string) $content('seo', 'canonical_url'));
        $canonicalUrl = filter_var($configuredCanonical, FILTER_VALIDATE_URL) ? $configuredCanonical : url()->current();
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => $canonicalUrl.'#product',
            'url' => $canonicalUrl,
            'name' => $content('seo', 'schema_name'),
            'description' => $content('seo', 'schema_description'),
            'sku' => $content('seo', 'schema_sku'),
            'category' => $content('seo', 'schema_category'),
            'image' => [
                $sectionImage('hero', 'asset/images/furniture-polish-before-after.webp'),
                $sectionImage('hero', 'asset/images/hero-bed-comparison.webp', 'image_1'),
                $sectionImage('hero', 'asset/images/furniture-polish-combo.webp', 'image_2'),
            ],
            'brand' => [
                '@type' => 'Brand',
                'name' => $content('seo', 'schema_brand'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'price' => $content('seo', 'schema_offer_price'),
                'priceCurrency' => $content('seo', 'schema_price_currency'),
                'availability' => $content('seo', 'schema_availability'),
                'itemCondition' => $content('seo', 'schema_condition'),
            ],
        ];
        if ((string) $content('seo', 'schema_rating_enabled') === '1'
            && (float) $content('seo', 'schema_rating_value') > 0
            && (int) $content('seo', 'schema_review_count') > 0) {
            $schemaData['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $content('seo', 'schema_rating_value'),
                'reviewCount' => (int) $content('seo', 'schema_review_count'),
                'bestRating' => (float) $content('seo', 'schema_best_rating'),
            ];
        }
    @endphp
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="tracking-endpoint" content="{{ route('tracking.events') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $content('seo', 'meta_title') }}</title>
    <meta name="description" content="{{ $content('seo', 'meta_description') }}">
    <meta name="keywords" content="{{ $content('seo', 'meta_keywords') }}">
    <meta name="author" content="{{ $content('seo', 'meta_author') }}">
    <meta name="robots" content="{{ $content('seo', 'robots') }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="theme-color" content="{{ $content('site', 'primary_color') }}">
    @if (data_get($sections->get('site')?->content, 'favicon'))
        <link rel="icon" href="{{ $sectionImage('site', '', 'favicon') }}">
    @endif
    <meta property="og:locale" content="bn_BD">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $content('seo', 'og_title') }}">
    <meta property="og:description" content="{{ $content('seo', 'og_description') }}">
    <meta property="og:image" content="{{ $sectionImage('seo', 'asset/images/furniture-polish-combo.png') }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ $content('seo', 'og_site_name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $content('seo', 'og_title') }}">
    <meta name="twitter:description" content="{{ $content('seo', 'og_description') }}">
    <meta name="twitter:image" content="{{ $sectionImage('seo', 'asset/images/furniture-polish-combo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('asset/css/style.css') }}?v={{ file_exists(public_path('asset/css/style.css')) ? filemtime(public_path('asset/css/style.css')) : time() }}">
    <style>
        .reveal {
            opacity: 1 !important;
            transform: none !important;
        }
    </style>
    <link rel="stylesheet"
        href="{{ asset('asset/css/responsive.css') }}?v={{ filemtime(public_path('asset/css/responsive.css')) }}">
    @include('landing.appearance-styles')
    @include('landing.section-styles')
    <script type="application/ld+json">
  @json($schemaData)
  </script>
    @if (!empty($tracking['meta_pixel_id']))
        <script>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($tracking['meta_pixel_id']));
            fbq('track', 'PageView');
        </script>
    @endif
    @if (!empty($tracking['google_analytics_id']) || !empty($tracking['google_ads_id']))
        @php
            $googleId = !empty($tracking['google_analytics_id'])
                ? $tracking['google_analytics_id']
                : $tracking['google_ads_id'];
        @endphp
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($googleId) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments)
            }
            gtag('js', new Date());
            @if (!empty($tracking['google_analytics_id']))
                gtag('config', @json($tracking['google_analytics_id']));
            @endif
            @if (!empty($tracking['google_ads_id']))
                gtag('config', @json($tracking['google_ads_id']));
            @endif
        </script>
    @endif
    @if (!empty($tracking['google_tag_manager_id']))
        <script>
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f)
            })(window, document, 'script', 'dataLayer', @json($tracking['google_tag_manager_id']));
        </script>
    @endif
    @if (!empty($tracking['tiktok_pixel_id']))
        <script>
            ! function(w, d, t) {
                w.TiktokAnalyticsObject = t;
                var ttq = w[t] = w[t] || [];
                ttq.methods = ['page', 'track', 'identify', 'instances', 'debug', 'on', 'off', 'once', 'ready', 'alias',
                    'group', 'enableCookie', 'disableCookie'
                ];
                ttq.setAndDefer = function(t, e) {
                    t[e] = function() {
                        t.push([e].concat(Array.prototype.slice.call(arguments, 0)))
                    }
                };
                for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
                ttq.load = function(e) {
                    ttq._i = ttq._i || {};
                    ttq._i[e] = [];
                    ttq._i[e]._u = 'https://analytics.tiktok.com/i18n/pixel/events.js';
                    ttq._t = ttq._t || {};
                    ttq._t[e] = +new Date;
                    ttq._o = ttq._o || {};
                    ttq._o[e] = {};
                    var i = d.createElement('script');
                    i.async = true;
                    i.src = 'https://analytics.tiktok.com/i18n/pixel/events.js?sdkid=' + e + '&lib=' + t;
                    var n = d.getElementsByTagName('script')[0];
                    n.parentNode.insertBefore(i, n)
                };
                ttq.load(@json($tracking['tiktok_pixel_id']));
                ttq.page()
            }(window, document, 'ttq');
        </script>
    @endif
    @if (!empty($tracking['clarity_id']))
        <script>
            (function(c, l, a, r, i, t, y) {
                c[a] = c[a] || function() {
                    (c[a].q = c[a].q || []).push(arguments)
                };
                t = l.createElement(r);
                t.async = 1;
                t.src = 'https://www.clarity.ms/tag/' + i;
                y = l.getElementsByTagName(r)[0];
                y.parentNode.insertBefore(t, y)
            })(window, document, 'clarity', 'script', @json($tracking['clarity_id']));
        </script>
    @endif
    @if (!empty($tracking['custom_head_script']))
        {!! $tracking['custom_head_script'] !!}
    @endif
</head>

<body>
    @if (!empty($tracking['google_tag_manager_id']))
        <noscript><iframe
                src="https://www.googletagmanager.com/ns.html?id={{ urlencode($tracking['google_tag_manager_id']) }}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    @if (!empty($tracking['meta_pixel_id']))
        <noscript><img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id={{ urlencode($tracking['meta_pixel_id']) }}&ev=PageView&noscript=1"
                alt=""></noscript>
    @endif
    <!-- Top Announcement Bar -->
    @if(filled($content('hero', 'top_bar_text')))
        <div class="top-announcement-bar">
            <div class="container">
                <span>{{ $content('hero', 'top_bar_text') }}</span>
            </div>
        </div>
    @endif

    <main>
        <!-- Hero -->
        <section class="hero" id="home" data-section="hero">
            <div class="container">
                <div class="hero-text text-center mx-auto">
                    @if(filled($content('hero', 'badge')))
                        <div class="mb-3">
                            <span class="hero-badge">{{ $content('hero', 'badge') }}</span>
                        </div>
                    @endif
                    <h1 class="hero-title mb-3">{!! $content('hero', 'title') !!}</h1>
                    @if(filled($content('hero', 'description')))
                        <p class="hero-subtitle mx-auto mb-3">{{ $content('hero', 'description') }}</p>
                    @endif
                </div>
                @php
                    $heroContent = $sections->get('hero')?->content ?? [];
                    $heroSlides = [[
                        'url' => $sectionImage('hero', 'asset/images/furniture-polish-before-after.webp'),
                        'alt' => $content('hero', 'image_alt'),
                    ]];
                    foreach (\App\Models\LandingSection::heroImageKeys($heroContent) as $heroImageKey) {
                        $heroIndex = (int) substr($heroImageKey, 6);
                        $heroPath = data_get($heroContent, $heroImageKey);
                        $fallback = match ($heroIndex) {
                            1 => 'asset/images/hero-bed-comparison.webp',
                            2 => 'asset/images/furniture-polish-combo.webp',
                            default => null,
                        };
                        $heroImageExists = is_string($heroPath)
                            && $heroPath !== ''
                            && \Illuminate\Support\Facades\Storage::disk('public')->exists($heroPath);
                        if (!$heroImageExists && !$fallback) continue;
                        $heroSlides[] = [
                            'url' => $heroImageExists ? route('media.show', ['path' => $heroPath]) : asset($fallback),
                            'alt' => data_get($heroContent, $heroImageKey.'_alt') ?: $content('hero', $heroImageKey.'_alt') ?: 'Hero slider image '.($heroIndex + 1),
                        ];
                    }
                @endphp
                <div class="hero-visual hero-slider text-center mx-auto"
                    aria-label="{{ $content('hero', 'image_alt') }}" data-hero-slider
                    data-autoplay="{{ $content('hero', 'slider_autoplay') }}"
                    data-interval="{{ $content('hero', 'slider_interval') ?: 4 }}">
                    <div class="hero-slider-track">
                        @foreach ($heroSlides as $heroSlide)
                            <div class="hero-slide" @if(!$loop->first) aria-hidden="true" @endif>
                                <img class="hero-product-image hero-product" src="{{ $heroSlide['url'] }}"
                                    alt="{{ $heroSlide['alt'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                    width="1358" height="798"
                                    @if($loop->first) fetchpriority="high" @endif>
                            </div>
                        @endforeach
                    </div>
                    <div class="hero-slider-dots" aria-label="{{ $content('hero', 'slider_group_label') }}">
                        @foreach ($heroSlides as $heroSlide)
                            <button type="button" @class(['active' => $loop->first])
                                aria-label="{{ $content('hero', 'slider_item_label') }} {{ $loop->iteration }}"
                                @if($loop->first) aria-current="true" @endif></button>
                        @endforeach
                    </div>
                </div>
                @if(filled($content('hero', 'offer_text')) || filled($content('hero', 'offer_highlight')))
                    <p class="hero-offer-copy mx-auto">
                        @if(filled($content('hero', 'offer_text')))
                            <span class="hero-offer-copy__primary">{{ $content('hero', 'offer_text') }}</span>
                        @endif
                        @if(filled($content('hero', 'offer_highlight')))
                            <span class="hero-offer-copy__accent">{{ $content('hero', 'offer_highlight') }}</span>
                        @endif
                    </p>
                @endif
            </div>
        </section>

        <!-- Video -->
        @php
            $videoUrl = trim((string) $content('video', 'video_url'));
            $videoFile = data_get($sections->get('video')?->content, 'video_file');
            $videoSource = \App\Support\VideoSource::fromUrl($videoUrl);
            $isShortVideo = (bool) preg_match('~(?:youtube\.com|youtu\.be)/(?:shorts/)?~i', $videoUrl)
                && str_contains((string) parse_url($videoUrl, PHP_URL_PATH), '/shorts/');
        @endphp
        <section class="video-section section-pad" data-section="video" aria-label="{{ $content('video', 'section_label') }}">
            <div class="container">
                <div class="text-center mb-5"><span
                        class="section-kicker mb-3">{{ $content('video', 'kicker') }}</span>
                    <h2 class="section-title">{{ $content('video', 'title') }}</h2>
                    <p class="section-copy">{{ $content('video', 'description') }}</p>
                </div>
                <div class="video-frame reveal{{ $isShortVideo ? ' video-frame--short' : '' }}">
                    @if ($videoSource || $videoFile)
                        @include('landing.video-player', [
                            'source' => $videoSource ?? [
                                'type' => 'file',
                                'url' => route('media.show', ['path' => $videoFile]),
                            ],
                            'title' => $content('video', 'title'),
                            'poster' => $sectionImage('video', 'asset/images/furniture-polish-combo.webp'),
                            'fallbackText' => $content('video', 'video_fallback_text'),
                            'soundLabel' => $content('video', 'sound_button_label'),
                            'soundEnabledLabel' => $content('video', 'sound_enabled_label'),
                            'autoplay' => false,
                        ])
                    @else
                        <div class="video-placeholder"
                            style="background-image:linear-gradient(135deg,rgba(20,11,4,.25),rgba(20,11,4,.68)),url('{{ $sectionImage('video', 'asset/images/furniture-polish-combo.webp') }}')"
                            role="img" aria-label="{{ $content('video', 'poster_alt') }}">
                            <span class="video-play" aria-hidden="true"><i class="bi bi-play-fill"></i></span>
                            <span class="video-placeholder-copy">{{ $content('video', 'placeholder_text') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Product benefits -->
        <section class="section-pad" id="features" data-section="features">
            <div class="container">
                <div class="text-center mx-auto mb-5" style="max-width:720px"><span
                        class="section-kicker mb-3">{{ $content('features', 'kicker') }}</span>
                    <h2 class="section-title">{{ $content('features', 'title') }}</h2>
                    <p class="section-copy">{{ $content('features', 'description') }}</p>
                </div>
                <div class="row g-3 g-md-4">
                    <div class="col-6 col-lg-4 reveal">
                        <article class="benefit-card">
                            <div class="icon-box"><i class="bi {{ $content('features', 'card_1_icon') }}"></i></div>
                            @if (data_get($sections->get('features')?->content, 'image_1'))
                                <img src="{{ $sectionImage('features', '', 'image_1') }}"
                                    alt="{{ $content('features', 'card_1_title') }}" class="img-fluid rounded mb-3"
                                    loading="lazy">
                            @endif
                            <h3>{{ $content('features', 'card_1_title') }}</h3>
                            <p>{{ $content('features', 'card_1_text') }}</p>
                        </article>
                    </div>
                    <div class="col-6 col-lg-4 reveal">
                        <article class="benefit-card">
                            <div class="icon-box"><i class="bi {{ $content('features', 'card_2_icon') }}"></i></div>
                            @if (data_get($sections->get('features')?->content, 'image_2'))
                                <img src="{{ $sectionImage('features', '', 'image_2') }}"
                                    alt="{{ $content('features', 'card_2_title') }}" class="img-fluid rounded mb-3"
                                    loading="lazy">
                            @endif
                            <h3>{{ $content('features', 'card_2_title') }}</h3>
                            <p>{{ $content('features', 'card_2_text') }}</p>
                        </article>
                    </div>
                    <div class="col-6 col-lg-4 reveal">
                        <article class="benefit-card">
                            <div class="icon-box"><i class="bi {{ $content('features', 'card_3_icon') }}"></i></div>
                            @if (data_get($sections->get('features')?->content, 'image_3'))
                                <img src="{{ $sectionImage('features', '', 'image_3') }}"
                                    alt="{{ $content('features', 'card_3_title') }}" class="img-fluid rounded mb-3"
                                    loading="lazy">
                            @endif
                            <h3>{{ $content('features', 'card_3_title') }}</h3>
                            <p>{{ $content('features', 'card_3_text') }}</p>
                        </article>
                    </div>
                    <div class="col-6 col-lg-4 reveal">
                        <article class="benefit-card">
                            <div class="icon-box"><i class="bi {{ $content('features', 'card_4_icon') }}"></i></div>
                            @if (data_get($sections->get('features')?->content, 'image_4'))
                                <img src="{{ $sectionImage('features', '', 'image_4') }}"
                                    alt="{{ $content('features', 'card_4_title') }}" class="img-fluid rounded mb-3"
                                    loading="lazy">
                            @endif
                            <h3>{{ $content('features', 'card_4_title') }}</h3>
                            <p>{{ $content('features', 'card_4_text') }}</p>
                        </article>
                    </div>
                    <div class="col-6 col-lg-4 reveal">
                        <article class="benefit-card">
                            <div class="icon-box"><i class="bi {{ $content('features', 'card_5_icon') }}"></i></div>
                            @if (data_get($sections->get('features')?->content, 'image_5'))
                                <img src="{{ $sectionImage('features', '', 'image_5') }}"
                                    alt="{{ $content('features', 'card_5_title') }}" class="img-fluid rounded mb-3"
                                    loading="lazy">
                            @endif
                            <h3>{{ $content('features', 'card_5_title') }}</h3>
                            <p>{{ $content('features', 'card_5_text') }}</p>
                        </article>
                    </div>
                    <div class="col-6 col-lg-4 reveal">
                        <article class="benefit-card">
                            <div class="icon-box"><i class="bi {{ $content('features', 'card_6_icon') }}"></i></div>
                            @if (data_get($sections->get('features')?->content, 'image_6'))
                                <img src="{{ $sectionImage('features', '', 'image_6') }}"
                                    alt="{{ $content('features', 'card_6_title') }}" class="img-fluid rounded mb-3"
                                    loading="lazy">
                            @endif
                            <h3>{{ $content('features', 'card_6_title') }}</h3>
                            <p>{{ $content('features', 'card_6_text') }}</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ingredients -->
        <section class="section-pad bg-cream" data-section="story">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 reveal">
                        <div class="feature-image-wrap"><img class="feature-image"
                                src="{{ $sectionImage('story', 'asset/images/furniture-polish-combo.webp') }}" loading="lazy"
                                width="520" height="520" alt="{{ $content('story', 'image_alt') }}"></div>
                    </div>
                    <div class="col-lg-6 reveal"><span
                            class="section-kicker mb-3">{{ $content('story', 'kicker') }}</span>
                        <h2 class="section-title mb-3">{{ $content('story', 'title') }}</h2>
                        <p class="section-copy mb-4">{{ $content('story', 'description') }}
                        </p>
                        <div class="feature-list">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="feature-item"><i
                                        class="bi bi-check2"></i>{{ $content('story', "list_{$i}") }}</div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Premium packaging comparison -->
        <section class="package-comparison section-pad" data-section="package_comparison"
            aria-labelledby="packageComparisonTitle">
            <div class="container">
                <div class="package-comparison-grid">
                    <div class="package-comparison-media reveal">
                        <img src="{{ $sectionImage('package_comparison', 'asset/images/furniture-polish-combo.webp') }}"
                            alt="{{ $content('package_comparison', 'image_alt') }}" loading="lazy" width="520"
                            height="520">
                    </div>
                    <div class="package-comparison-content reveal">
                        <span class="package-comparison-badge">{{ $content('package_comparison', 'badge') }}</span>
                        <h2 id="packageComparisonTitle" class="package-comparison-title section-title">
                            {{ $content('package_comparison', 'title_prefix') }}
                            <span>{{ $content('package_comparison', 'title_accent') }}</span>
                        </h2>
                        <p class="package-comparison-copy">{{ $content('package_comparison', 'description') }}</p>

                        <div class="package-table-wrap">
                            <table class="package-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ $content('package_comparison', 'feature_heading') }}</th>
                                        <th scope="col"><span>{{ $content('package_comparison', 'premium_heading') }}</span><small>{{ $content('package_comparison', 'premium_subheading') }}</small></th>
                                        <th scope="col"><span>{{ $content('package_comparison', 'regular_heading') }}</span><small>{{ $content('package_comparison', 'regular_subheading') }}</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= 4; $i++)
                                        <tr>
                                            <td>{{ $content('package_comparison', "feature_{$i}") }}</td>
                                            <td><span class="comparison-yes" aria-label="{{ $content('package_comparison', 'yes_label') }}">{{ $content('package_comparison', 'yes_mark') }}</span></td>
                                            <td><span class="comparison-no" aria-label="{{ $content('package_comparison', 'no_label') }}">{{ $content('package_comparison', 'no_mark') }}</span></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Complete care package and usage -->
        <section class="complete-care-section" data-section="complete_care" aria-labelledby="completeCareTitle">
            <div class="complete-care-package">
                <div class="container">
                    <div class="complete-care-heading text-center reveal">
                        <h2 id="completeCareTitle" class="section-title">{{ $content('complete_care', 'title_prefix') }} <span>{{ $content('complete_care', 'title_accent') }}</span></h2>
                        <p>{{ $content('complete_care', 'subtitle') }}</p>
                    </div>

                    <ul class="package-contents reveal">
                        @for ($i = 1; $i <= 5; $i++)
                            @php
                                $careItem = explode('—', (string) $content('complete_care', "item_{$i}"), 2);
                            @endphp
                            <li>
                                <span class="package-item-copy">
                                    <strong>{{ trim($careItem[0]) }}</strong>
                                    @if(isset($careItem[1]))
                                        <span class="package-item-description">— {{ trim($careItem[1]) }}</span>
                                    @endif
                                </span>
                            </li>
                        @endfor
                    </ul>

                    <div class="complete-care-cta reveal">
                        <a class="btn complete-care-order" href="{{ $content('complete_care', 'button_link') ?: '#Order' }}">
                            <svg class="hero-cart-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.4L21 7H6.1M9.5 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm9 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" />
                            </svg>
                            {{ $content('complete_care', 'button') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="care-usage">
                <div class="container">
                    <h2 class="care-usage-title section-title text-center reveal">{{ $content('complete_care', 'usage_title_prefix') }} <span>{{ $content('complete_care', 'usage_title_accent') }}</span></h2>
                    <div class="care-usage-grid">
                        @for ($i = 1; $i <= 6; $i++)
                            <div class="care-usage-card reveal"><i class="bi {{ $content('complete_care', "usage_{$i}_icon") }}"></i><span>{{ $content('complete_care', "usage_{$i}") }}</span></div>
                        @endfor
                    </div>
                    <div class="care-guarantee reveal">
                        <strong>{{ $content('complete_care', 'guarantee_title') }}</strong>
                        <span>{{ $content('complete_care', 'guarantee_text') }} <small>{{ $content('complete_care', 'guarantee_note') }}</small></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Popular menu -->
        <section class="section-pad" id="menu" data-section="menu">
            <div class="container">
                <div class="d-md-flex justify-content-between align-items-end mb-5">
                    <div><span class="section-kicker mb-3">{{ $content('menu', 'kicker') }}</span>
                        <h2 class="section-title mb-2">{{ $content('menu', 'title') }}</h2>
                        <p class="section-copy mb-0">{{ $content('menu', 'description') }}</p>
                    </div><a class="text-orange fw-bold mt-3 mt-md-0 d-inline-block"
                        href="{{ $content('menu', 'button_link') ?: '#Order' }}">{{ $content('menu', 'order_link_text') }}
                        <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-4">
                    <div class="col-sm-6 col-lg-4 reveal">
                        <article class="menu-card">
                            <div class="menu-image-wrap"><span
                                    class="best-tag">{{ $content('menu', 'popular_tag') }}</span><img
                                    class="menu-image"
                                    src="{{ $sectionImage('menu', 'asset/images/furniture-polish-combo.webp', 'image_1') }}"
                                    loading="lazy" width="240" height="240"
                                    alt="{{ $content('menu', 'item_1_alt') }}"></div>
                            <h3>{{ $content('menu', 'item_1_name') }}</h3>
                            <p>{{ $content('menu', 'item_1_text') }}</p>
                            <div class="d-flex justify-content-between align-items-center"><span
                                    class="menu-price">{{ $content('site', 'currency_symbol') }}{{ $content('menu', 'item_1_price') }}</span><a
                                    href="{{ $content('menu', 'button_link') ?: '#Order' }}"
                                    class="btn btn-order py-2 px-3">{{ $content('menu', 'order_button_text') }}</a>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-4 reveal">
                        <article class="menu-card">
                            <div class="menu-image-wrap"><img class="menu-image"
                                    src="{{ $sectionImage('menu', 'asset/images/furniture-polish-combo.webp', 'image_2') }}"
                                    loading="lazy" width="240" height="240"
                                    alt="{{ $content('menu', 'item_2_alt') }}"></div>
                            <h3>{{ $content('menu', 'item_2_name') }}</h3>
                            <p>{{ $content('menu', 'item_2_text') }}</p>
                            <div class="d-flex justify-content-between align-items-center"><span
                                    class="menu-price">{{ $content('site', 'currency_symbol') }}{{ $content('menu', 'item_2_price') }}</span><a
                                    href="{{ $content('menu', 'button_link') ?: '#Order' }}"
                                    class="btn btn-order py-2 px-3">{{ $content('menu', 'order_button_text') }}</a>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-4 reveal">
                        <article class="menu-card">
                            <div class="menu-image-wrap"><img class="menu-image"
                                    src="{{ $sectionImage('menu', 'asset/images/furniture-polish-combo.webp', 'image_3') }}"
                                    loading="lazy" width="240" height="240"
                                    alt="{{ $content('menu', 'item_3_alt') }}"></div>
                            <h3>{{ $content('menu', 'item_3_name') }}</h3>
                            <p>{{ $content('menu', 'item_3_text') }}</p>
                            <div class="d-flex justify-content-between align-items-center"><span
                                    class="menu-price">{{ $content('site', 'currency_symbol') }}{{ $content('menu', 'item_3_price') }}</span><a
                                    href="{{ $content('menu', 'button_link') ?: '#Order' }}"
                                    class="btn btn-order py-2 px-3">{{ $content('menu', 'order_button_text') }}</a>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery -->
        <section class="section-pad bg-cream" data-section="gallery">
            <div class="container">
                <div class="text-center mb-5"><span
                        class="section-kicker mb-3">{{ $content('gallery', 'kicker') }}</span>
                    <h2 class="section-title">{{ $content('gallery', 'title') }}</h2>
                </div>
                <div class="review-slider gallery-slider"
                    data-interval="{{ $content('gallery', 'slider_interval') ?: 3 }}"
                    data-autoplay="{{ $content('gallery', 'slider_autoplay') }}"
                    data-pause-label="{{ $content('gallery', 'slider_pause_label') }}"
                    data-resume-label="{{ $content('gallery', 'slider_resume_label') }}"
                    aria-label="{{ $content('gallery', 'title') }}">
                    <div class="review-track" tabindex="0" aria-label="{{ $content('gallery', 'track_label') }}">
                        @php
                            $galleryFallbacks = [
                                'furniture-polish-combo.webp',
                                'furniture-polish-combo.webp',
                                'furniture-polish-combo.webp',
                                'furniture-polish-combo.webp',
                                'furniture-polish-combo.webp',
                                'furniture-polish-combo.webp',
                            ];
                        @endphp
                        @foreach (\App\Models\LandingSection::galleryImageKeys($sections->get('gallery')?->content ?? []) as $imageKey)
                            @php
                                $n = (int) substr($imageKey, 6);
                                $fallback = $galleryFallbacks[($n - 1) % count($galleryFallbacks)];
                            @endphp
                            <div class="review-slide">
                                <div class="gallery-item"><img
                                        src="{{ $sectionImage('gallery', 'asset/images/' . $fallback, 'image_' . $n) }}"
                                        loading="lazy" width="320" height="320"
                                        alt="{{ $content('gallery', 'image_' . $n . '_alt') }}"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="review-controls" hidden>
                        <button type="button" data-review-prev
                            aria-label="{{ $content('gallery', 'slider_prev_label') }}">←</button>
                        <button type="button" data-review-pause
                            aria-label="{{ $content('gallery', 'slider_pause_label') }}">{{ $content('gallery', 'slider_pause_label') }}</button>
                        <button type="button" data-review-next
                            aria-label="{{ $content('gallery', 'slider_next_label') }}">→</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Customer reviews -->
        <section class="section-pad" data-section="reviews">
            <div class="container">
                <div class="row align-items-end mb-5">
                    <div class="col-lg-8"><span
                            class="section-kicker mb-3">{{ $content('reviews', 'kicker') }}</span>
                        <h2 class="section-title">{{ $content('reviews', 'title') }}</h2>
                    </div>
                    @if ((string) $content('reviews', 'rating_summary_visible') === '1')
                        <div class="col-lg-4 mt-3 mt-lg-0">
                            <div class="rating-summary d-flex align-items-center justify-content-between">
                                <div><strong>{{ $content('reviews', 'rating') }}</strong>
                                    <div class="stars">{{ $content('reviews', 'rating_stars') }}</div>
                                </div><span>{{ $content('reviews', 'rating_count') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
                @php
                    $reviewImages = collect(
                        \App\Models\LandingSection::reviewImageKeys($sections->get('reviews')?->content ?? []),
                    )->filter(fn (string $imageKey): bool => $storedMediaExists(
                        data_get($sections->get('reviews')?->content, $imageKey),
                    ));
                    $reviewContent = array_merge(
                        \App\Models\LandingSection::defaults('reviews'),
                        $sections->get('reviews')?->content ?? [],
                    );
                    $reviewItems = \App\Models\LandingSection::reviewItems($reviewContent);
                @endphp
                <div class="review-slider" data-autoplay="{{ $content('reviews', 'slider_autoplay') }}"
                    data-interval="{{ $content('reviews', 'slider_interval') }}"
                    data-pause-label="{{ $content('reviews', 'slider_pause_label') }}"
                    data-resume-label="{{ $content('reviews', 'slider_resume_label') }}"
                    aria-label="{{ $content('reviews', 'carousel_label') }}">
                    <div class="review-track" tabindex="0" aria-label="{{ $content('reviews', 'track_label') }}">
                        @if ($reviewImages->isNotEmpty())
                            @foreach ($reviewImages as $imageKey)
                                <article class="review-slide review-image-card">
                                    <img src="{{ $sectionImage('reviews', '', $imageKey) }}"
                                        alt="{{ $content('reviews', 'review_image_alt') }} {{ $loop->iteration }}" loading="lazy">
                                </article>
                            @endforeach
                        @else
                            @forelse ($reviewItems as $review)
                                <article class="review-slide review-card">
                                    <div class="stars mb-3">{{ $review['rating'] }}</div>
                                    <p class="review-text">“{{ $review['text'] }}”</p>
                                    <div class="d-flex align-items-center gap-3 mt-4">
                                        @if (!empty($review['image_key']) && $storedMediaExists(data_get($sections->get('reviews')?->content, $review['image_key'])))
                                            <img class="avatar" style="object-fit:cover"
                                                src="{{ $sectionImage('reviews', '', $review['image_key']) }}"
                                                alt="{{ $review['name'] }}" loading="lazy">
                                        @else
                                            <div class="avatar">{{ $review['avatar'] }}</div>
                                        @endif
                                        <div><strong>{{ $review['name'] }}</strong>
                                            <div class="verified"><i class="bi bi-patch-check-fill"></i>
                                                {{ $content('reviews', 'verified_label') }}</div>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <p class="review-empty-message">{{ $content('reviews', 'no_reviews_text') }}</p>
                            @endforelse
                        @endif
                    </div>
                    <div class="review-controls" hidden>
                        <button type="button" data-review-prev
                            aria-label="{{ $content('reviews', 'slider_prev_label') }}">←</button>
                        <button type="button" data-review-pause
                            aria-label="{{ $content('reviews', 'slider_pause_label') }}">{{ $content('reviews', 'slider_pause_label') }}</button>
                        <button type="button" data-review-next
                            aria-label="{{ $content('reviews', 'slider_next_label') }}">→</button>
                    </div>
                </div>
                <script src="{{ asset('asset/js/review-slider.js') }}?v={{ filemtime(public_path('asset/js/review-slider.js')) }}"
                    defer></script>
            </div>
        </section>

        @include('landing.video-reviews')

        <!-- Special offer -->
        <section class="section-pad pt-0" data-section="deal">
            <div class="container">
                <div class="deal-box reveal">
                    <div class="row align-items-center position-relative" style="z-index:1">
                        <div class="col-lg-7"><span class="offer-badge mb-3">{{ $content('deal', 'kicker') }}</span>
                            <h2 class="section-title mb-3">{{ $content('deal', 'title') }}</h2>
                            <div
                                class="d-flex align-items-center justify-content-center justify-content-lg-start gap-3 mb-4">
                                <span
                                    class="price-old text-white-50">{{ $content('site', 'currency_symbol') }}{{ $content('deal', 'old_price') }}</span><span
                                    class="price-new">{{ $content('site', 'currency_symbol') }}{{ $content('deal', 'price') }}</span><span
                                    class="badge rounded-pill text-bg-danger fs-6">{{ $content('deal', 'discount') }}</span>
                            </div>
                            <div class="deal-points mb-4">
                                @for ($i = 1; $i <= 3; $i++)
                                    <span class="deal-point"><i class="bi bi-check-circle-fill me-1"></i>
                                        {{ $content('deal', "benefit_{$i}") }}</span>
                                @endfor
                            </div>
                            <a class="btn btn-order btn-lg px-5"
                                href="{{ $content('deal', 'button_link') ?: '#Order' }}">{{ $content('deal', 'button') }}
                                <i class="bi bi-arrow-right ms-2"></i></a>
                        </div>
                        <div class="col-lg-5 d-none d-lg-block"><img
                                src="{{ $sectionImage('deal', 'asset/images/furniture-polish-combo.webp') }}" loading="lazy"
                                width="520" height="347" alt="{{ $content('deal', 'image_alt') }}"
                                style="filter:drop-shadow(0 22px 16px rgba(0,0,0,.35))"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Order form -->
        <section class="section-pad bg-cream" id="Order" data-section="order">
            <div class="container">
                <div class="text-center mx-auto mb-5" style="max-width:720px"><span
                        class="section-kicker mb-3">{{ $content('order', 'kicker') }}</span>
                    <h2 class="section-title">{{ $content('order', 'title') }}</h2>
                    <p class="section-copy">{{ $content('order', 'description') }}</p>
                </div>
                <div class="product-picker reveal" id="productPicker">
                    <h3>{{ $content('order', 'products_title') }}</h3>
                    <div class="product-options">
                        @forelse($products as $index => $product)
                            <label @class([
                                'product-option',
                                'selected' => $index === 0,
                                'has-badge' => filled($product->badge),
                                'has-package-title',
                            ])>
                                <span class="product-package-title">{{ $product->pickerPackageName() }}</span>
                                @if ($product->badge)
                                    <span class="product-tag">{{ $product->badge }}</span>
                                @endif
                                <input class="product-radio" type="radio" name="product_choice"
                                    value="{{ $product->id }}" data-price="{{ $product->price }}"
                                    data-name="{{ $product->name }}" data-image="{{ $product->imageUrl() }}"
                                    {{ $index === 0 ? 'checked' : '' }}>
                                <img src="{{ $product->imageUrl() }}" width="74" height="74"
                                    alt="{{ $product->name }}">
                                @if (filled($product->pickerPackageDetails()))
                                    <span class="product-name">{{ $product->pickerPackageDetails() }}</span>
                                @endif
                                <span class="product-quantity" aria-label="{{ $product->name }} — {{ $content('order', 'quantity_prefix') }}">
                                    <button class="product-qty-minus" type="button"
                                        aria-label="{{ $content('order', 'quantity_decrease_label') }}">−</button>
                                    <input class="product-qty" type="number" value="1" min="1"
                                        max="9999" inputmode="numeric" aria-label="{{ $content('order', 'quantity_prefix') }}">
                                    <button class="product-qty-plus" type="button"
                                        aria-label="{{ $content('order', 'quantity_increase_label') }}">+</button>
                                </span>
                                <span class="product-price">
                                    @if ($product->displayRegularPrice())
                                        <span class="product-regular-price">{{ $content('order', 'regular_price_label') }} <del>{{ $content('site', 'currency_symbol') }}{{ number_format($product->displayRegularPrice()) }}</del></span>
                                    @endif
                                    <strong class="product-offer-price"
                                        data-offer-label="{{ $content('order', 'offer_price_label') }}"
                                        data-currency="{{ $content('site', 'currency_symbol') }}">{{ $content('order', 'offer_price_label') }} {{ $content('site', 'currency_symbol') }}{{ number_format($product->price) }}</strong>
                                </span>
                            </label>
                        @empty
                            <p class="p-4 mb-0 text-center">{{ $content('order', 'products_empty') }}</p>
                        @endforelse
                    </div>
                </div>
                <div class="order-shell reveal">
                    <div class="row g-0">
                        <div class="col-lg-5">
                            <aside class="order-info text-center text-lg-start"><span
                                    class="badge bg-white text-danger rounded-pill mb-3">{{ $content('order', 'offer_badge') }}</span>
                                <h3 class="display-6 fw-bold">{{ $content('order', 'price_prefix') }} <span
                                        id="selectedOrderTotal">{{ $content('site', 'currency_symbol') }}{{ ($products->first()?->price ?? 0) + (int) $content('order', 'inside_delivery_charge') }}</span>
                                </h3>
                                <p class="mb-2"><i
                                        class="bi bi-check-circle-fill me-2"></i>{{ $content('order', 'benefit_1') }}
                                </p>
                                <p><i class="bi bi-check-circle-fill me-2"></i>{{ $content('order', 'benefit_2') }}
                                </p><img id="orderInfoImage"
                                    data-custom-image="{{ data_get($sections->get('order')?->content, 'image') ? '1' : '0' }}"
                                    src="{{ data_get($sections->get('order')?->content, 'image') ? $sectionImage('order', '') : $products->first()?->imageUrl() ?? $sectionImage('order', 'asset/images/furniture-polish-combo.webp') }}"
                                    loading="lazy" width="360" height="360"
                                    alt="{{ data_get($sections->get('order')?->content, 'image') ? $content('order', 'image_alt') : $products->first()?->name ?? $content('order', 'image_alt') }}">
                            </aside>
                        </div>
                        <div class="col-lg-7">
                            <form class="order-form needs-validation" id="orderForm"
                                data-incomplete-action="{{ route('incomplete-orders.store', [], false) }}"
                                action="{{ route('orders.store', [], false) }}" method="POST"
                                data-sending-message="{{ $content('order', 'sending_message') }}"
                                data-success-message="{{ $content('order', 'success_message') }}"
                                data-error-message="{{ $content('order', 'error_message') }}"
                                data-session-expired-message="{{ $content('order', 'session_expired_message') }}"
                                data-rate-limit-message="{{ $content('order', 'rate_limit_message') }}"
                                data-server-error-message="{{ $content('order', 'server_error_message') }}"
                                data-confirmation-error-message="{{ $content('order', 'confirmation_error_message') }}" novalidate>
                                @csrf
                                <input id="incompleteToken" name="incomplete_token" type="hidden"
                                    value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                <input id="productId" name="product_id" type="hidden"
                                    value="{{ $products->first()?->id }}">
                                <input id="qty" name="quantity" type="hidden" value="1">
                                @if ($products->isNotEmpty())
                                    <div class="selected-product-summary" aria-live="polite"
                                        data-currency="{{ $content('site', 'currency_symbol') }}"
                                        data-quantity-prefix="{{ $content('order', 'quantity_prefix') }}">
                                        <img id="selectedProductImage" src="{{ $products->first()->imageUrl() }}"
                                            width="76" height="76" alt="{{ $products->first()->name }}">
                                        <span><small>{{ $content('order', 'selected_product_label') }}</small><strong
                                                id="selectedProductName">{{ $products->first()->name }}</strong><em
                                                id="selectedProductQuantity">{{ $content('order', 'quantity_prefix') }}:
                                                ১</em></span>
                                        <b
                                            id="selectedProductPrice">{{ $content('site', 'currency_symbol') }}{{ number_format($products->first()->price) }}</b>
                                    </div>
                                @endif
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label"
                                            for="name">{{ $content('order', 'name_label') }}</label><input
                                            class="form-control" id="name" name="name" type="text"
                                            placeholder="{{ $content('order', 'name_placeholder') }}"
                                            autocomplete="name" required>
                                        <div class="invalid-feedback">{{ $content('order', 'name_error') }}</div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label"
                                            for="phone">{{ $content('order', 'phone_label') }}</label><input
                                            class="form-control" id="phone" name="phone" type="tel"
                                            inputmode="numeric"
                                            placeholder="{{ $content('order', 'phone_placeholder') }}"
                                            autocomplete="tel" required>
                                        <div class="invalid-feedback">{{ $content('order', 'phone_error') }}</div>
                                    </div>
                                    <div class="col-12"><label class="form-label"
                                            for="address">{{ $content('order', 'address_label') }}</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"
                                            placeholder="{{ $content('order', 'address_placeholder') }}" autocomplete="street-address" required></textarea>
                                        <div class="invalid-feedback">{{ $content('order', 'address_error') }}</div>
                                    </div>
                                    <fieldset class="col-12 delivery-fieldset">
                                        <legend class="form-label">{{ $content('order', 'delivery_area_label') }}
                                        </legend>
                                        <div class="delivery-options">
                                            <label class="delivery-option selected">
                                                <input type="radio" name="delivery_area" value="inside_dhaka"
                                                    data-charge="{{ $content('order', 'inside_delivery_charge') }}"
                                                    checked required>
                                                <span class="delivery-check"><i class="bi bi-check-lg"></i></span>
                                                <span><strong>{{ $content('order', 'inside_dhaka_label') }}</strong><small>{{ $content('order', 'delivery_charge_label') }}</small></span>
                                                <b>{{ $content('site', 'currency_symbol') }}{{ $content('order', 'inside_delivery_charge') }}</b>
                                            </label>
                                            <label class="delivery-option">
                                                <input type="radio" name="delivery_area" value="outside_dhaka"
                                                    data-charge="{{ $content('order', 'outside_delivery_charge') }}"
                                                    required>
                                                <span class="delivery-check"><i class="bi bi-check-lg"></i></span>
                                                <span><strong>{{ $content('order', 'outside_dhaka_label') }}</strong><small>{{ $content('order', 'delivery_charge_label') }}</small></span>
                                                <b>{{ $content('site', 'currency_symbol') }}{{ $content('order', 'outside_delivery_charge') }}</b>
                                            </label>
                                        </div>
                                    </fieldset>
                                <div class="col-12 order-submit-wrap"><button class="btn btn-order btn-lg w-100"
                                        type="submit" @disabled($products->isEmpty())><i
                                            class="bi bi-bag-check me-2"></i>{{ $content('order', 'form_button') }}</button>
                                    <p class="form-note text-center mt-3 mb-0"><i
                                            class="bi bi-lock me-1"></i>{{ $content('order', 'privacy') }}</p>
                                    <div id="formStatus" class="alert alert-success mt-3 mb-0 d-none" role="status"
                                        aria-live="polite">{{ $content('order', 'success_message') }}</div>
                                </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            </div>
        </section>

        <!-- Frequently asked questions -->
        <section class="section-pad" id="faq" data-section="faq">
            <div class="container">
                <div class="row g-5">
                    <div class="col-lg-5"><span class="section-kicker mb-3">{{ $content('faq', 'kicker') }}</span>
                        <h2 class="section-title">{{ $content('faq', 'title') }}</h2>
                        <p class="section-copy">{{ $content('faq', 'description') }}</p><a
                            href="tel:{{ $content('site', 'phone_link') }}" class="fw-bold text-orange"><i
                                class="bi bi-telephone me-2"></i>{{ $content('site', 'phone') }}</a>
                    </div>
                    <div class="col-lg-7">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header"><button class="accordion-button" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#faq1">{{ $content('faq', 'question_1') }}</button></h3>
                                <div id="faq1" class="accordion-collapse collapse show"
                                    data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">{{ $content('faq', 'answer_1') }}</div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h3 class="accordion-header"><button class="accordion-button collapsed"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq2">{{ $content('faq', 'question_2') }}</button></h3>
                                <div id="faq2" class="accordion-collapse collapse"
                                    data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">{{ $content('faq', 'answer_2') }}</div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h3 class="accordion-header"><button class="accordion-button collapsed"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq3">{{ $content('faq', 'question_3') }}</button></h3>
                                <div id="faq3" class="accordion-collapse collapse"
                                    data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">{{ $content('faq', 'answer_3') }}</div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h3 class="accordion-header"><button class="accordion-button collapsed"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq4">{{ $content('faq', 'question_4') }}</button></h3>
                                <div id="faq4" class="accordion-collapse collapse"
                                    data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">{{ $content('faq', 'answer_4') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final call to action -->
        <section class="pb-5" data-section="cta">
            <div class="container">
                <div class="final-cta text-center reveal">
                    <p class="fw-bold mb-2">{{ $content('cta', 'eyebrow') }}</p>
                    <h2 class="mb-4">{{ $content('cta', 'title') }}</h2><a class="btn-light-cta"
                        href="{{ $content('cta', 'button_link') ?: '#Order' }}">{{ $content('cta', 'button') }} <i
                            class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </section>
    </main>

    <dialog id="orderSuccessModal" data-addon-url="{{ session('addon_url', '') }}" class="order-success-modal"
        data-added-label="{{ $content('order', 'modal_added_label') }}"
        data-adding-label="{{ $content('order', 'modal_adding_label') }}"
        data-adding-message="{{ $content('order', 'modal_adding_message') }}"
        data-confirm-title="{{ $content('order', 'modal_title') }}"
        data-confirm-description="{{ $content('order', 'modal_description') }}"
        data-total-label="{{ $content('order', 'modal_total_label') }}"
        data-order-required-message="{{ $content('order', 'order_required_message') }}"
        data-session-expired-message="{{ $content('order', 'session_expired_message') }}"
        data-rate-limit-message="{{ $content('order', 'rate_limit_message') }}"
        data-addon-expired-message="{{ $content('order', 'addon_expired_message') }}"
        data-addon-server-error-message="{{ $content('order', 'addon_server_error_message') }}"
        data-addon-error-message="{{ $content('order', 'addon_error_message') }}" aria-labelledby="orderSuccessTitle"
        aria-describedby="orderSuccessMessage"
        data-open-on-load="{{ session('order_success') ? 'true' : 'false' }}">
        <button type="button" class="success-close" aria-label="{{ $content('order', 'modal_close_button') }}"
            data-close-success>×</button>
        <div class="success-message">
            @if (data_get($sections->get('order')?->content, 'modal_image'))
                <img class="success-custom-image" src="{{ $sectionImage('order', '', 'modal_image') }}"
                    alt="{{ $content('order', 'modal_image_alt') }}" loading="lazy">
            @else
                <div class="success-icon" aria-hidden="true">✓</div>
            @endif
            <h2 id="orderSuccessTitle">{{ $content('order', 'modal_title') }}</h2>
            <p id="orderSuccessMessage">{{ $content('order', 'modal_description') }}</p>
            @php
                $modalPhone = $content('order', 'modal_phone') ?: $content('site', 'phone');
                $modalPhoneLink =
                    $content('order', 'modal_phone_link') ?:
                    ($content('order', 'modal_phone') ?:
                    $content('site', 'phone_link'));
            @endphp
            <a class="success-phone" href="tel:{{ $modalPhoneLink }}"><i class="bi bi-telephone-fill"
                    aria-hidden="true"></i> {{ $modalPhone }}</a>
            <p class="success-reminder">{{ $content('order', 'modal_reminder') }}</p>
        </div>
        <section class="success-recommendations" aria-labelledby="successProductsTitle">
            <h3 id="successProductsTitle">{{ $content('order', 'modal_products_title') }}</h3>
            <p class="text-secondary">{{ $content('order', 'modal_products_description') }}</p>
            <p class="success-reminder">{{ $content('order', 'modal_delivery_note') }}</p>
            <p id="modalOrderStatus" role="status" aria-live="polite" hidden></p>
            <div class="success-product-grid">
                @foreach ($modalProducts as $product)
                    <article class="success-product-card" data-recommendation-id="{{ $product->id }}">
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" width="240"
                            height="180" loading="lazy">
                        <div class="success-product-details">
                            <h4>{{ $product->name }}</h4>
                            <strong>{{ $content('site', 'currency_symbol') }}{{ number_format($product->price) }}</strong>
                            <label class="modal-quantity-wrap">
                                <span>{{ $content('order', 'modal_quantity_label') }}</span>
                                <span class="modal-quantity-control">
                                    <button class="modal-qty-minus" type="button" aria-label="{{ $content('order', 'quantity_decrease_label') }}">−</button>
                                    <input class="modal-product-quantity" type="number" min="1" max="9999"
                                        value="1" inputmode="numeric" aria-label="{{ $product->name }} — {{ $content('order', 'quantity_prefix') }}">
                                    <button class="modal-qty-plus" type="button" aria-label="{{ $content('order', 'quantity_increase_label') }}">+</button>
                                </span>
                            </label>
                            <button type="button" class="btn btn-order w-100"
                                data-buy-product="{{ $product->id }}">{{ $content('order', 'modal_buy_button') }}
                                <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                        </div>
                    </article>
                @endforeach
            </div>
            <p id="successProductsEmpty" class="text-secondary" hidden>
                {{ $content('order', 'modal_products_empty') }}</p>
        </section>
        <button type="button" class="btn btn-outline-secondary success-dismiss"
            data-close-success>{{ $content('order', 'modal_close_button') }}</button>
    </dialog>

    <!-- Footer -->
    @if ((string) $content('site', 'footer_visible') !== '0')
        <footer>
            <div class="text-center mb-3">
                @if (data_get($sections->get('site')?->content, 'image'))
                    <img src="{{ $sectionImage('site', '') }}" alt="{{ $content('site', 'site_name') }}"
                        class="site-footer-logo" style="max-width:160px;max-height:72px"
                    loading="lazy">@else<strong>{{ $content('site', 'site_name') }}</strong>
                @endif
            </div>
            <div class="container">
                <p class="small mb-0 text-center">{{ $content('site', 'copyright') }}</p>
            </div>
        </footer>
    @endif

    <!-- Mobile WhatsApp contact -->
    @if ((string) $content('social', 'whatsapp_visible') !== '0')
        <div class="mobile-order-bar d-md-none">
            <a class="mobile-whatsapp-button whatsapp" href="{{ $content('social', 'whatsapp_link') }}"
                target="_blank" rel="noopener noreferrer"
                aria-label="{{ $content('social', 'whatsapp_label') }}">
                <i class="bi bi-whatsapp" aria-hidden="true"></i>
            </a>
            @if ((string) $content('site', 'mobile_order_visible') !== '0')
                <a class="btn btn-order mobile-order-cta"
                    href="{{ $content('site', 'mobile_order_link') ?: '#Order' }}">
                    {{ $content('site', 'mobile_order_text') }}
                </a>
            @endif
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script id="trackingConfig" type="application/json">@json(array_diff_key($tracking, array_flip(['custom_head_script', 'custom_body_script'])))</script>
    <script src="{{ asset('asset/js/tracking.js') }}?v={{ filemtime(public_path('asset/js/tracking.js')) }}"></script>
    <script
        src="{{ asset('asset/js/visitor-events.js') }}?v={{ filemtime(public_path('asset/js/visitor-events.js')) }}">
    </script>
    <script
        src="{{ asset('asset/js/incomplete-checkout.js') }}?v={{ filemtime(public_path('asset/js/incomplete-checkout.js')) }}">
    </script>
    <script
        src="{{ asset('asset/js/script.js') }}?v={{ file_exists(public_path('asset/js/script.js')) ? filemtime(public_path('asset/js/script.js')) : time() }}">
    </script>
    <script src="{{ asset('asset/js/hero-slider.js') }}?v={{ filemtime(public_path('asset/js/hero-slider.js')) }}"
        defer></script>
    <script src="{{ asset('asset/js/delivery-area.js') }}?v={{ filemtime(public_path('asset/js/delivery-area.js')) }}">
    </script>
    <noscript>
        <style>
            .reveal {
                opacity: 1 !important;
                transform: none !important;
            }
        </style>
    </noscript>
    <script>
        setTimeout(function() {
            document.querySelectorAll('.reveal:not(.show)').forEach(function(el) {
                el.classList.add('show');
            });
        }, 400);
    </script>
    @if (!empty($tracking['linkedin_partner_id']))
        <script>
            _linkedin_partner_id = @json($tracking['linkedin_partner_id']);
            window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
            window._linkedin_data_partner_ids.push(_linkedin_partner_id);
        </script>
        <script async src="https://snap.licdn.com/li.lms-analytics/insight.min.js"></script>
    @endif
    @if (!empty($tracking['pinterest_tag_id']))
        <script>
            ! function(e) {
                if (!window.pintrk) {
                    window.pintrk = function() {
                        window.pintrk.queue.push(Array.prototype.slice.call(arguments))
                    };
                    var n = window.pintrk;
                    n.queue = [];
                    n.version = '3.0';
                    var t = document.createElement('script');
                    t.async = !0;
                    t.src = e;
                    var r = document.getElementsByTagName('script')[0];
                    r.parentNode.insertBefore(t, r)
                }
            }('https://s.pinimg.com/ct/core.js');
            pintrk('load', @json($tracking['pinterest_tag_id']));
            pintrk('page');
        </script>
    @endif
    @if (!empty($tracking['snapchat_pixel_id']))
        <script>
            (function(e, t, n) {
                if (e.snaptr) return;
                var a = e.snaptr = function() {
                    a.handleRequest ? a.handleRequest.apply(a, arguments) : a.queue.push(arguments)
                };
                a.queue = [];
                var s = 'script',
                    r = t.createElement(s);
                r.async = !0;
                r.src = n;
                var u = t.getElementsByTagName(s)[0];
                u.parentNode.insertBefore(r, u)
            })(window, document, 'https://sc-static.net/scevent.min.js');
            snaptr('init', @json($tracking['snapchat_pixel_id']));
            snaptr('track', 'PAGE_VIEW');
        </script>
    @endif
    @if (!empty($tracking['custom_body_script']))
        {!! $tracking['custom_body_script'] !!}
    @endif
</body>

</html>
