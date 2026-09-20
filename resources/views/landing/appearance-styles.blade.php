@php
    $appearance = array_replace(\App\Models\LandingSection::defaults('site'), $sections->get('site')?->content ?? []);
    $themeColors = [
        'primary_color' => '--orange',
        'primary_dark_color' => '--orange-dark',
        'button_end_color' => '--button-end',
        'accent_color' => '--red',
        'text_color' => '--ink',
        'muted_color' => '--muted',
        'surface_color' => '--cream',
        'page_color' => '--page-background',
    ];
@endphp
<style>
    :root {
        @foreach ($themeColors as $key => $variable)
            @if (preg_match('/^#[a-fA-F0-9]{6}$/', $appearance[$key] ?? ''))
                {{ $variable }}: {{ $appearance[$key] }};
            @endif
        @endforeach
    }

    html {
        font-size: {{ max(12, min(32, (int) ($appearance['base_font_size'] ?: 16))) }}px;
    }

    body {
        background: var(--page-background, #fff);
        font-family: {!! ($appearance['font_family'] ?? 'hind') === 'system'
            ? 'system-ui, sans-serif'
            : "'Hind Siliguri', sans-serif" !!};
    }

    .btn-order {
        color: {{ preg_match('/^#[a-fA-F0-9]{6}$/', $appearance['button_text_color'] ?? '') ? $appearance['button_text_color'] : '#ffffff' }} !important;
        border-radius: {{ max(0, min(100, (int) ($appearance['button_radius'] ?? 14))) }}px !important;
    }

    .site-footer-logo {
        max-width: {{ max(32, min(400, (int) ($appearance['logo_width'] ?? 160))) }}px !important;
        width: 100%;
        height: auto;
        object-fit: contain;
    }

    @foreach (['footer_background_color' => 'background-color', 'footer_text_color' => 'color'] as $key => $property)
        @if (preg_match('/^#[a-fA-F0-9]{6}$/', $appearance[$key] ?? ''))
            footer {
                {{ $property }}: {{ $appearance[$key] }} !important;
            }
        @endif
    @endforeach
    @if (($appearance['button_hover_enabled'] ?? '1') === '0')
        :is(button, .btn, .btn-light-cta, .float-btn, .success-phone):hover {
            transform: none !important;
            filter: none !important;
            scale: none !important;
            box-shadow: none !important;
        }
    @endif
    @media(max-width:767px) {
        html {
            font-size: {{ max(12, min(32, (int) ($appearance['mobile_font_size'] ?: 16))) }}px;
        }

        body {
            font-size: 1rem;
        }
    }
</style>
