@php
    $heroIndex = is_numeric($i) ? (int) $i : null;
    $heroSlideNumber = $heroIndex === null ? 'নতুন' : $heroIndex + 1;
    $heroFallback = match ($heroIndex) {
        1 => 'asset/images/hero-bed-comparison.png',
        2 => 'asset/images/furniture-polish-combo.png',
        default => null,
    };
@endphp
<div class="review-upload-card hero-upload-card field">
    <label for="{{ $imageKey }}">Hero slider image {{ $heroSlideNumber }}</label>
    <img id="preview-{{ $imageKey }}" class="preview"
        @if(data_get($section->content, $imageKey)) src="{{ route('media.show', ['path' => data_get($section->content, $imageKey)]) }}"
        @elseif($heroFallback && !array_key_exists($imageKey, $section->content ?? [])) src="{{ asset($heroFallback) }}"
        @else hidden @endif alt="Hero slider image {{ $heroSlideNumber }} preview">
    <input id="{{ $imageKey }}" type="file" name="{{ $imageKey }}" accept="image/jpeg,image/png,image/webp"
        data-image-preview="preview-{{ $imageKey }}" aria-describedby="help-{{ $imageKey }}">
    @include('dasgboard.pages.image-upload-help')
    @error($imageKey)<small role="alert" style="color:#dc3545">{{ $message }}</small>@enderror
    <label for="{{ $imageKey }}_alt">ছবির বর্ণনা (Alt text)</label>
    <input id="{{ $imageKey }}_alt" name="{{ $imageKey }}_alt" type="text" maxlength="1000"
        value="{{ old($imageKey.'_alt', data_get($section->content, $imageKey.'_alt')) }}">
    @error($imageKey.'_alt')<small role="alert" style="color:#dc3545">{{ $message }}</small>@enderror
    <label class="review-remove"><input type="checkbox" name="remove_{{ $imageKey }}" value="1"
        @checked(old('remove_'.$imageKey))> এই slide সরান</label>
</div>
