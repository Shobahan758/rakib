@php
    $scope = '[data-section="'.$sectionSlug.'"][data-section]';
    $copySelector = ':is(.section-copy, .review-text, .hero-readable-text, .menu-card p, .benefit-card p, .accordion-body, .feature-chip, .feature-list, .package-comparison-copy, .package-table, .complete-care-heading p, .package-contents, .care-usage-card)';
    $imageSelector = match ($sectionSlug) {
        'hero' => '.hero-product', 'story' => '.feature-image', 'menu' => '.menu-image',
        'gallery' => '.gallery-item img', 'reviews' => '.review-image-card img',
        'features' => '.benefit-card > img', 'deal' => '.deal-box img',
        'order' => '#orderInfoImage', 'video' => '.video-placeholder',
        'package_comparison' => '.package-comparison-media img', default => null,
    };
@endphp
@if(in_array($settings['section_text_align'] ?? '', ['left', 'center', 'right'], true))
    {!! $scope !!} :is(h1,h2,h3,p,.section-kicker,.feature-list) { text-align:{{ $settings['section_text_align'] }}!important; }
@endif
@if(is_numeric($settings['section_line_height'] ?? null))
    {!! $scope !!} :is(h1,h2,h3,p,.feature-chip,.feature-list,.accordion-body) { line-height:{{ max(1.2,min(3,(float)$settings['section_line_height'])) }}!important; }
@endif
@if($validColor('section_text_color'))
    {!! $scope !!} {!! $copySelector !!} { color:{{ $settings['section_text_color'] }}!important; }
@endif
@if(is_numeric($settings['section_text_size'] ?? null))
    {!! $scope !!} {!! $copySelector !!} { font-size:{{ max(12,min(32,(int)$settings['section_text_size'])) }}px!important; }
@endif
@foreach(['section_button_color' => 'background', 'section_button_text_color' => 'color'] as $key => $property)
    @if($validColor($key))
        {!! $scope !!} :is(.btn-order,.btn-light-cta,.complete-care-order,.review-controls button,.video-review-controls button) { {{ $property }}:{{ $settings[$key] }}!important; }
    @endif
@endforeach
@if(is_numeric($settings['section_button_radius'] ?? null))
    {!! $scope !!} :is(.btn-order,.btn-light-cta,.complete-care-order,.review-controls button,.video-review-controls button) { border-radius:{{ max(0,min(100,(int)$settings['section_button_radius'])) }}px!important; }
@endif
@foreach(['section_card_color' => 'background', 'section_card_text_color' => 'color'] as $key => $property)
    @if($validColor($key))
        {!! $scope !!} :is(.feature-chip,.benefit-card,.menu-card,.review-card,.review-image-card,.accordion-item,.accordion-button,.order-form,.order-info,.deal-box,.package-table-wrap,.care-usage-card) { {{ $property }}:{{ $settings[$key] }}!important; }
        @if($property === 'color')
            {!! $scope !!} :is(.benefit-card,.menu-card,.review-card,.accordion-item,.order-form,.order-info,.deal-box) :is(p,h3,label) { color:{{ $settings[$key] }}!important; }
        @endif
    @endif
@endforeach
@if($imageSelector)
    {!! $scope !!} {!! $imageSelector !!} {
        @foreach(['section_image_width' => 'max-width', 'section_image_height' => 'height', 'section_image_radius' => 'border-radius'] as $key => $property)
            @if(is_numeric($settings[$key] ?? null)) {{ $property }}:{{ max(0,min(3000,(int)$settings[$key])) }}px!important; @endif
        @endforeach
        @if(in_array($settings['section_image_fit'] ?? '', ['contain','cover'], true)) {{ $sectionSlug === 'video' ? 'background-size' : 'object-fit' }}:{{ $settings['section_image_fit'] }}!important; @endif
    }
    @if(in_array($sectionSlug, ['menu','gallery','reviews'], true) && is_numeric($settings['section_image_height'] ?? null))
        {!! $scope !!} :is(.menu-image-wrap,.gallery-item,.review-image-card) { height:auto!important;aspect-ratio:auto; }
    @endif
@endif
@if($sectionSlug === 'hero')
    @if(($settings['readable_background_visible'] ?? '1') === '0')
        {!! $scope !!} .hero-readable-text { background:transparent!important; }
    @elseif($validColor('readable_background_color'))
        {!! $scope !!} .hero-readable-text { background:{{ $settings['readable_background_color'] }}!important; }
    @endif
@endif
@if($sectionSlug === 'complete_care')
    @if(!empty($settings['background_image']))
        {!! $scope !!} :is(.complete-care-package,.care-usage) { background:transparent!important; }
    @elseif($validColor('section_background_color'))
        {!! $scope !!} :is(.complete-care-package,.care-usage) { background-color:{{ $settings['section_background_color'] }}!important;background-image:none!important; }
    @endif
@endif
@media(max-width:767px) {
    @if(is_numeric($settings['section_mobile_text_size'] ?? null))
        {!! $scope !!} {!! $copySelector !!} { font-size:{{ max(12,min(32,(int)$settings['section_mobile_text_size'])) }}px!important; }
    @endif
    @if($imageSelector && is_numeric($settings['section_mobile_image_height'] ?? null))
        {!! $scope !!} {!! $imageSelector !!} { height:{{ max(0,min(3000,(int)$settings['section_mobile_image_height'])) }}px!important; }
        @if(in_array($sectionSlug, ['menu','gallery','reviews'], true))
            {!! $scope !!} :is(.menu-image-wrap,.gallery-item,.review-image-card) { height:auto!important;aspect-ratio:auto; }
        @endif
    @endif
    @if($sectionSlug === 'complete_care' && !empty($settings['mobile_background_image']))
        {!! $scope !!} :is(.complete-care-package,.care-usage) { background:transparent!important; }
    @endif
}
