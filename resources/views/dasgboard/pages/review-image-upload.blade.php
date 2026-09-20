        <div class="review-upload-card field" data-saved="{{ data_get($section->content, $imageKey) ? 'true' : 'false' }}" data-invalid="{{ $errors->has($imageKey) ? 'true' : 'false' }}">
            <label for="{{ $imageKey }}">রিভিউ ছবি {{ $i }}</label>
            <img id="preview-{{ $imageKey }}" class="preview" @if(data_get($section->content, $imageKey)) src="{{ route('media.show', ['path' => data_get($section->content, $imageKey)]) }}" @else hidden @endif alt="রিভিউ ছবি {{ $i }}">
            <input id="{{ $imageKey }}" type="file" name="{{ $imageKey }}" accept="image/jpeg,image/png,image/webp" data-image-preview="preview-{{ $imageKey }}" aria-describedby="help-{{ $imageKey }}">
            @include('dasgboard.pages.image-upload-help')
            @if(data_get($section->content, $imageKey))<label class="review-remove"><input type="checkbox" name="remove_{{ $imageKey }}" value="1" @checked(old('remove_'.$imageKey))> এই ছবি সরান</label>@endif
            @error($imageKey)<small role="alert" style="color:#dc3545">{{ $message }}</small>@enderror
        </div>
