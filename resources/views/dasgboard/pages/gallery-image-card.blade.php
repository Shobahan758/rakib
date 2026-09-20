<div class="review-upload-card field">
    <label for="{{ $imageKey }}">গ্যালারির ছবি {{ $i }}</label>
    <img id="preview-{{ $imageKey }}" class="preview"
        @if(data_get($section->content, $imageKey)) src="{{ route('media.show', ['path' => data_get($section->content, $imageKey)]) }}"
        @elseif(is_int($i) && $i <= 6) src="{{ asset('asset/images/'.$galleryFallbacks[$i - 1]) }}"
        @else hidden @endif alt="গ্যালারির ছবি {{ $i }}">
    <input id="{{ $imageKey }}" type="file" name="{{ $imageKey }}" accept="image/jpeg,image/png,image/webp" data-image-preview="preview-{{ $imageKey }}" aria-describedby="help-{{ $imageKey }}">
    @include('dasgboard.pages.image-upload-help')
    @error($imageKey)<small role="alert" style="color:#dc3545">{{ $message }}</small>@enderror
    <label for="{{ $imageKey }}_alt">ছবির বর্ণনা (Alt text)</label>
    <input id="{{ $imageKey }}_alt" name="{{ $imageKey }}_alt" type="text" maxlength="1000" value="{{ old($imageKey.'_alt', data_get($section->content, $imageKey.'_alt')) }}">
    @error($imageKey.'_alt')<small role="alert" style="color:#dc3545">{{ $message }}</small>@enderror
    <label class="review-remove"><input type="checkbox" name="remove_{{ $imageKey }}" value="1" @checked(old('remove_'.$imageKey))> এই ছবি সরান</label>
</div>
