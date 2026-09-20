<style>
    @foreach (\App\Models\LandingSection::definitions() as $sectionSlug => $definition)
        @continue(in_array($sectionSlug, ['site', 'social', 'seo'], true))

        @php($settings = $sections->get($sectionSlug)?->content ?? []) @php($validColor = fn($key) => preg_match('/^#[a-fA-F0-9]{6}$/', $settings[$key] ?? '')) [data-section="{{ $sectionSlug }}"] {
            @if ($sections->get($sectionSlug)?->is_visible === false)
                display: none !important;
            @endif
            @foreach (['section_min_height' => 'min-height', 'section_padding_top' => 'padding-top', 'section_padding_bottom' => 'padding-bottom'] as $key => $property)
                @if (isset($settings[$key]) && $settings[$key] !== '' && is_numeric($settings[$key]))
                    {{ $property }}: {{ max(0, min(3000, (int) $settings[$key])) }}px !important;
                @endif
            @endforeach
            @if ($validColor('section_background_color'))
                background-color: {{ $settings['section_background_color'] }} !important;

                @if (empty($settings['background_image']))
                    background-image: none !important;
                @endif
            @endif
            @if (!empty($settings['background_image']))
                background-image: url("{{ route('media.show', ['path' => $settings['background_image']]) }}") !important;
                background-size: cover !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
            @endif
            @if (in_array($settings['section_background_position'] ?? '', ['center', 'top', 'bottom', 'left', 'right'], true))
                background-position: {{ $settings['section_background_position'] }} !important;
            @endif
            @if (in_array($settings['section_background_size'] ?? '', ['cover', 'contain', 'auto'], true))
                background-size: {{ $settings['section_background_size'] }} !important;
            @endif

        }

        @if ($validColor('section_text_color'))
            [data-section="{{ $sectionSlug }}"],
            [data-section="{{ $sectionSlug }}"] .section-copy,
            [data-section="{{ $sectionSlug }}"] .text-secondary,
            [data-section="{{ $sectionSlug }}"][data-section] .hero-readable-text {
                color: {{ $settings['section_text_color'] }} !important;
            }
        @endif
        @if ($validColor('section_heading_color'))
            [data-section="{{ $sectionSlug }}"] h1,
            [data-section="{{ $sectionSlug }}"] h2,
            [data-section="{{ $sectionSlug }}"] h3 {
                color: {{ $settings['section_heading_color'] }} !important;
            }
        @endif
        @if (is_numeric($settings['section_heading_size'] ?? null))
            [data-section="{{ $sectionSlug }}"] h1,
            [data-section="{{ $sectionSlug }}"] h2,
            [data-section="{{ $sectionSlug }}"] h3,
            [data-section="{{ $sectionSlug }}"] .section-title {
                font-size: {{ max(16, min(120, (int) $settings['section_heading_size'])) }}px !important;
            }
        @endif
        @if (is_numeric($settings['section_text_size'] ?? null))
            [data-section="{{ $sectionSlug }}"] .section-copy,
            [data-section="{{ $sectionSlug }}"] .review-text {
                font-size: {{ max(12, min(32, (int) $settings['section_text_size'])) }}px !important;
            }
        @endif
        @include('landing.section-design-styles') @media(max-width:767px) {
            @if (is_numeric($settings['section_mobile_heading_size'] ?? null))
                [data-section="{{ $sectionSlug }}"] h1,
                [data-section="{{ $sectionSlug }}"] h2,
                [data-section="{{ $sectionSlug }}"] h3,
                [data-section="{{ $sectionSlug }}"] .section-title {
                    font-size: {{ max(16, min(120, (int) $settings['section_mobile_heading_size'])) }}px !important;
                }
            @endif
            @if (is_numeric($settings['section_mobile_text_size'] ?? null))
                [data-section="{{ $sectionSlug }}"] .section-copy,
                [data-section="{{ $sectionSlug }}"] .review-text {
                    font-size: {{ max(12, min(32, (int) $settings['section_mobile_text_size'])) }}px !important;
                }
            @endif
            [data-section="{{ $sectionSlug }}"] {
                @if (!empty($settings['mobile_background_image']))
                    background-image: url("{{ route('media.show', ['path' => $settings['mobile_background_image']]) }}") !important;
                    background-size: {{ in_array($settings['section_background_size'] ?? '', ['cover', 'contain', 'auto'], true) ? $settings['section_background_size'] : 'cover' }} !important;
                    background-position: {{ in_array($settings['section_background_position'] ?? '', ['center', 'top', 'bottom', 'left', 'right'], true) ? $settings['section_background_position'] : 'center' }} !important;
                    background-repeat: no-repeat !important;
                @endif
                @foreach (['section_mobile_min_height' => 'min-height', 'section_mobile_padding_top' => 'padding-top', 'section_mobile_padding_bottom' => 'padding-bottom'] as $key => $property)
                    @if (isset($settings[$key]) && $settings[$key] !== '' && is_numeric($settings[$key]))
                        {{ $property }}: {{ max(0, min(3000, (int) $settings[$key])) }}px !important;
                    @endif
                @endforeach
            }
        }
    @endforeach
</style>
