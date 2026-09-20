@php
    $imageSpecification = \App\Support\ImageUploadSpecification::for($slug, $imageKey);
@endphp
<small class="image-upload-help" id="{{ $helpId ?? 'help-'.$imageKey }}">
    <strong>প্রস্তাবিত মাপ: {{ $imageSpecification['width'] }} × {{ $imageSpecification['height'] }} px (চওড়া × উচ্চতা)</strong><br>
    @if(str_starts_with($imageKey, 'review_image_'))
        JPG, PNG, WebP — রিভিউ ছবির সংখ্যা ও ফাইল সাইজে অ্যাপের নির্দিষ্ট সীমা নেই। হোস্টিংয়ের আপলোড সীমা প্রযোজ্য।
    @else
        JPG, PNG, WebP — সর্বোচ্চ {{ $maxImageMb ?? 4 }} MB।
    @endif
    {{ $imageSpecification['advice'] }}
</small>
