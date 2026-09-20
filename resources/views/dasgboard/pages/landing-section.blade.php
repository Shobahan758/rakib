@extends('dasgboard.layouts.app')
@section('title', $definition['label'])
@push('styles')
<style>
.saved-video-preview iframe,.saved-video-preview video{display:block;width:100%;height:100%;border:0;object-fit:contain}

.edit-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,320px);gap:22px}.field{margin-bottom:18px}.field label{display:block;margin-bottom:7px;font-weight:600}.field input:not([type="checkbox"]),.field textarea{width:100%;padding:12px;border:1px solid var(--line);border-radius:9px;font:inherit}.field textarea{min-height:120px}.field select{width:100%;padding:12px;border:1px solid var(--line);border-radius:9px;font:inherit}.save,.add-review,.remove-review{padding:12px 20px;border:0;border-radius:9px;font:inherit;font-weight:600;cursor:pointer}.save,.add-review{background:var(--primary);color:#fff}.review-heading{display:flex;align-items:center;justify-content:space-between;margin:28px 0 14px}.review-heading h2{margin:0}.review-item{position:relative;margin-bottom:16px;padding:20px;border:1px solid var(--line);border-radius:12px;background:#f9fcfb}.review-item h3{margin:0 0 16px}.review-grid{display:grid;grid-template-columns:1fr 1fr 120px;gap:14px}.review-grid .review-text{grid-column:1/-1}.remove-review{position:absolute;right:16px;top:14px;padding:7px 11px;background:#fff0f0;color:var(--danger)}.alert{margin-bottom:18px;padding:12px;border-radius:9px;background:#fff0e3;color:var(--primary-dark)}.preview{max-width:100%;border-radius:10px}@media(max-width:850px){.edit-grid,.review-grid{grid-template-columns:1fr}.review-grid .review-text{grid-column:auto}}
.review-upload-card[hidden]{display:none}.review-upload-panel{margin-bottom:24px}.review-upload-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.review-upload-card{min-width:0;padding:16px;border:1px solid var(--line);border-radius:12px}.review-upload-card .preview{display:block;width:100%;height:210px;object-fit:contain;margin-bottom:12px;background:#fff7ef}.review-upload-card .preview[hidden]{display:none}.review-upload-card small{display:block;margin-top:8px;color:var(--muted)}.review-upload-card .review-remove{margin-top:12px}.review-text-settings{margin-bottom:20px;padding:14px;border:1px solid var(--line);border-radius:10px}.review-text-settings summary{cursor:pointer;font-weight:600;margin-bottom:14px}@media(max-width:1200px){.review-upload-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.review-upload-grid{grid-template-columns:minmax(0,1fr)}.review-upload-panel .save{width:100%}}
.hero-upload-card .preview{height:auto;max-height:none;aspect-ratio:1358/798;object-fit:contain}
</style>
@endpush
@section('content')
<div class="page-heading"><div><h1>{{ $definition['label'] }}</h1><p>লেখা, ছবি, রং, বাটন ও মোবাইল ডিজাইন পরিবর্তন করুন। খালি ডিজাইন ফিল্ডে ডিফল্ট থাকবে।</p><a href="{{ route('home') }}" target="_blank" rel="noopener">ওয়েবসাইট দেখুন ↗</a></div></div>
@if($errors->any())<div role="alert" class="alert"><strong>পরিবর্তন সেভ হয়নি:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($slug === 'order')
    @include('dasgboard.pages.products.settings-links')
@endif
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.landing.update', $slug) }}">@csrf @method('PUT')
@if($definition['review_images'] ?? false)
<section class="panel review-upload-panel">
    <h2>রিভিউ ছবির স্লাইডার</h2>
    <p>এখানে রিভিউয়ের স্ক্রিনশট দিন। ছবি থাকলে ফ্রন্টএন্ডে লেখার কার্ডের বদলে ছবি দেখাবে। দুই বা তার বেশি ছবি দিলে প্রতি ৪ সেকেন্ডে অটো স্লাইড হবে।</p>
    <div class="review-upload-grid">
    @php
        $reviewImageKeys = array_unique(array_merge(
            array_map(fn ($i) => "review_image_{$i}", range(1, 6)),
            \App\Models\LandingSection::reviewImageKeys($section->content ?? []),
        ));
        natsort($reviewImageKeys);
    @endphp
    @foreach($reviewImageKeys as $imageKey)
        @php
            $i = $loop->iteration;
        @endphp
        @include('dasgboard.pages.review-image-upload')
    @endforeach
    </div>
    <button class="add-review" id="addReviewImage" type="button" hidden>＋ Add Image / ছবির কার্ড যোগ করুন</button>
    <template id="reviewImageTemplate">
        @include('dasgboard.pages.review-image-upload', ['imageKey' => 'review_image___INDEX__', 'i' => '__INDEX__'])
    </template>
    <p>ছবির সংখ্যার নির্দিষ্ট সীমা নেই। বেশি ছবি হলে কয়েকবারে আপলোড করে সেভ করুন।</p>
    <p>প্রতিটি ছবি একটি আলাদা কার্ডে দেখাবে। সব ছবি সরালে নিচের লেখার রিভিউগুলো আবার দেখাবে। ছবি নির্বাচন করার পর সেভ করুন।</p>
    <button class="save" type="submit"><i class="fa-solid fa-floppy-disk"></i> রিভিউ ছবি ও সেটিংস সেভ করুন</button>
</section>
@endif
@if($slug === 'gallery')
    @include('dasgboard.pages.gallery-image-upload')
@endif
@if($slug === 'hero')
    @include('dasgboard.pages.hero-image-upload')
@endif
<div class="edit-grid"><section class="panel">
@foreach($definition['fields'] as $key => [$label, $type])
    @continue($key === 'video_url' || (in_array($slug, ['gallery', 'hero'], true) && preg_match('/^image_[1-9][0-9]*_alt$/D', $key)))
    @if(($definition['review_images'] ?? false) && $key === 'review_1_rating')<details class="review-text-settings"><summary>ছবি না থাকলে দেখানোর লেখার রিভিউ</summary>@endif
    @if($key === 'modal_title')
        <h2 id="order-success-settings">অর্ডার সফল হওয়ার পপআপ ও Success Page</h2>
        <p>ধন্যবাদ বার্তা, ফোন নম্বর, রিমাইন্ডার, অতিরিক্ত পণ্যের লেখা এবং success page-এর বাটন/label এখান থেকে পরিবর্তন করুন।</p>
        <div class="field">
            <label for="modal_image">পপআপের ছবি (ঐচ্ছিক)</label>
            <input id="modal_image" type="file" name="modal_image" accept="image/png,image/jpeg,image/webp" data-image-preview="preview-modal_image" aria-describedby="help-modal_image">
            @include('dasgboard.pages.image-upload-help', ['imageKey' => 'modal_image'])
            <img id="preview-modal_image" class="preview" @if(data_get($section->content, 'modal_image')) src="{{ route('media.show', ['path' => data_get($section->content, 'modal_image')]) }}" @else hidden @endif alt="পপআপের ছবি">
            @if(data_get($section->content, 'modal_image'))<label><input type="checkbox" name="remove_modal_image" value="1" @checked(old('remove_modal_image'))> ছবি সরিয়ে আগের টিক চিহ্ন দেখান</label>@endif
            @error('modal_image')<small style="color:#dc3545">{{ $message }}</small>@enderror
        </div>
    @endif
    @if($key === 'primary_color')<h2>সাইটের রং ও ফন্ট</h2><p>সাইটের ব্র্যান্ড রং, লেখার মাপ এবং ফুটার/মোবাইল অর্ডার বাটন নিয়ন্ত্রণ করুন।</p>@endif
    @if($key === 'slider_autoplay')<h2>স্লাইডারের সেটিংস</h2><p>অটো স্লাইড, প্রতি স্লাইডের সময় (২–৬০ সেকেন্ড) ও বাটনের লেখা পরিবর্তন করুন।</p>@endif
    @if($key === 'section_text_align')<h2>সেকশনের ডিজাইন ও মোবাইল লেআউট</h2><p>খালি রাখলে আগের ডিজাইন থাকবে। 0 দিলে ন্যূনতম উচ্চতা/ফাঁকা জায়গা থাকবে না। কনটেন্ট বেশি হলে সেকশন স্বয়ংক্রিয়ভাবে বড় হবে।</p>@endif
    <div class="field">
        <label for="{{ $key }}">{{ $label }}</label>
        @if($type === 'select')
            <select id="{{ $key }}" name="{{ $key }}">
                @foreach(\App\Models\LandingSection::selectOptions()[$key] ?? [] as $value => $text)
                    <option value="{{ $value }}" @selected((string) old($key, data_get($section->content, $key)) === (string) $value)>{{ $text }}</option>
                @endforeach
            </select>
        @elseif($type === 'color')
            <div class="color-control">
                <input type="color" data-color-target="{{ $key }}" aria-label="{{ $label }} picker" value="{{ preg_match('/^#[a-fA-F0-9]{6}$/', old($key, data_get($section->content, $key)) ?? '') ? old($key, data_get($section->content, $key)) : '#ff6b00' }}">
                <input id="{{ $key }}" name="{{ $key }}" type="text" pattern="#[a-fA-F0-9]{6}" maxlength="7" placeholder="#ff6b00" value="{{ old($key, data_get($section->content, $key)) }}">
            </div>
            <small>HEX রং দিন। খালি রাখলে ডিফল্ট রং থাকবে।</small>
        @elseif($type === 'textarea')
            <textarea id="{{ $key }}" name="{{ $key }}">{{ old($key, data_get($section->content, $key)) }}</textarea>
        @else
            <input id="{{ $key }}" name="{{ $key }}" type="{{ $type }}" @if($type === 'number') min="{{ $key === 'section_line_height' ? '1.2' : '0' }}" step="{{ $key === 'section_line_height' ? '0.1' : '1' }}" @if(array_key_exists($key, \App\Models\LandingSection::layoutFields())) max="{{ $key === 'section_line_height' ? 3 : (str_contains($key, 'heading_size') ? 120 : (str_contains($key, 'text_size') ? 32 : 3000)) }}" @endif @endif value="{{ old($key, data_get($section->content, $key)) }}">
        @endif
        @if($key === 'video_links')
            <small>প্রতি লাইনে একটি YouTube/Shorts অথবা MP4/WebM লিংক দিন (সর্বোচ্চ ২০টি)। লাইনের ক্রমেই ভিডিও দেখাবে। লিংক মুছলে ভিডিও সরবে। দুই বা বেশি ভিডিও দিলে অটো স্লাইড হবে। সব লিংক খালি রাখলে সেকশন দেখাবে না।</small>
        @endif
        @error($key)<small style="color:#dc3545">{{ $message }}</small>@enderror
    </div>
    @if(($definition['review_images'] ?? false) && $key === 'review_3_text')</details>@endif
@endforeach
@if($definition['dynamic_reviews'] ?? false)
    @php
        $reviewItems = old('reviews', data_get($section->content, 'reviews', []));
        $nextReviewIndex = count($reviewItems) ? max(array_map('intval', array_keys($reviewItems))) + 1 : 0;
    @endphp
    <div class="review-heading"><h2>Testimonials</h2><button class="add-review" id="addReview" type="button"><i class="fa-solid fa-plus"></i> Add Review</button></div>
    @error('reviews')<small style="display:block;margin-bottom:10px;color:#dc3545">{{ $message }}</small>@enderror
    <div id="reviewList">
        @foreach($reviewItems as $index => $review)
            <div class="review-item">
                <h3>Review {{ $loop->iteration }}</h3><button class="remove-review" type="button" aria-label="Delete review"><i class="fa-solid fa-trash"></i></button>
                <div class="review-grid">
                    <div class="field"><label>Customer name</label><input name="reviews[{{ $index }}][name]" value="{{ $review['name'] ?? '' }}" required></div>
                    <div class="field"><label>Location</label><input name="reviews[{{ $index }}][location]" value="{{ $review['location'] ?? '' }}"></div>
                    <div class="field"><label>Avatar letter</label><input name="reviews[{{ $index }}][avatar]" value="{{ $review['avatar'] ?? '' }}"></div>
                    <div class="field"><label>Rating</label><input name="reviews[{{ $index }}][rating]" value="{{ $review['rating'] ?? '★★★★★' }}" required></div>
                    <div class="field review-text"><label>Testimonial</label><textarea name="reviews[{{ $index }}][text]" required>{{ $review['text'] ?? '' }}</textarea></div>
                </div>
            </div>
        @endforeach
    </div>
@endif
</section><aside class="panel">
@if(!in_array($slug, ['site', 'social', 'seo'], true))
<div class="field"><input type="hidden" name="is_visible" value="0"><label><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $section->exists ? $section->is_visible : true))> Show section / সেকশন দেখান</label></div>
@endif
@php
    $imageInputs = [];
    if ($definition['image'] ?? false) $imageInputs['image'] = $slug === 'seo' ? 'Social sharing image' : ($slug === 'site' ? 'Site logo' : 'Section image');
    if (!in_array($slug, ['site', 'social', 'seo'], true)) $imageInputs += ['background_image' => 'Section background image', 'mobile_background_image' => 'Mobile background image (optional)'];
    if ($slug === 'site') $imageInputs['favicon'] = 'Browser icon / Favicon';
    for ($i = 1; $i <= (in_array($slug, ['gallery', 'hero'], true) ? 0 : ($definition['images'] ?? 0)); $i++) $imageInputs["image_{$i}"] = match ($slug) {
        'hero' => "Hero slider image ".($i + 1),
        'menu' => "Menu item {$i} image", 'features' => "Feature {$i} image", 'reviews' => "Customer {$i} photo", default => "Gallery image {$i}",
    };
@endphp
@foreach($imageInputs as $imageKey => $imageLabel)
<div class="field">
    <label for="{{ $imageKey }}">{{ $imageLabel }}</label>
    <input id="{{ $imageKey }}" type="file" name="{{ $imageKey }}" accept="image/jpeg,image/png,image/webp" data-image-preview="preview-{{ $imageKey }}" aria-describedby="help-{{ $imageKey }}">
    @include('dasgboard.pages.image-upload-help')
    <img id="preview-{{ $imageKey }}" class="preview" @if(data_get($section->content, $imageKey)) src="{{ route('media.show', ['path' => data_get($section->content, $imageKey)]) }}" @else hidden @endif alt="{{ $imageLabel }}">
    @if(data_get($section->content, $imageKey))<label><input type="checkbox" name="remove_{{ $imageKey }}" value="1" @checked(old('remove_'.$imageKey))> আপলোড করা ছবি সরিয়ে ডিফল্ট ব্যবহার করুন</label>@endif
    @error($imageKey)<small style="color:#dc3545">{{ $message }}</small>@enderror
</div>
@endforeach
@if($definition['video'] ?? false)
@include('dasgboard.pages.video-link-field')
@endif
@if($definition['video']??false)<div class="field"><label>Upload video (MP4, WebM or MOV; max 50 MB)</label><input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">@if(data_get($section->content,'video_file'))<video class="preview" src="{{ route('media.show', ['path' => data_get($section->content,'video_file')]) }}" controls preload="metadata"></video><label style="margin-top:10px;font-weight:500"><input type="checkbox" name="remove_video_file" value="1"> Remove uploaded video</label>@endif @error('video_file')<small style="color:#dc3545">{{ $message }}</small>@enderror</div>@endif
<button class="save" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button></aside></div></form>
@endsection
@if($definition['dynamic_reviews'] ?? false)
@push('scripts')
<script>
(() => {
    const list = document.getElementById('reviewList');
    let nextIndex = {{ $nextReviewIndex }};
    const renumber = () => list.querySelectorAll('.review-item h3').forEach((title, index) => title.textContent = `Review ${index + 1}`);
    document.getElementById('addReview').addEventListener('click', () => {
        const index = nextIndex++;
        const item = document.createElement('div');
        item.className = 'review-item';
        item.innerHTML = `<h3>Review</h3><button class="remove-review" type="button" aria-label="Delete review"><i class="fa-solid fa-trash"></i></button><div class="review-grid"><div class="field"><label>Customer name</label><input name="reviews[${index}][name]" required></div><div class="field"><label>Location</label><input name="reviews[${index}][location]"></div><div class="field"><label>Avatar letter</label><input name="reviews[${index}][avatar]"></div><div class="field"><label>Rating</label><input name="reviews[${index}][rating]" value="★★★★★" required></div><div class="field review-text"><label>Testimonial</label><textarea name="reviews[${index}][text]" required></textarea></div></div>`;
        list.appendChild(item); renumber(); item.querySelector('input').focus();
    });
    list.addEventListener('click', event => {
        const button = event.target.closest('.remove-review');
        if (!button) return;
        if (list.children.length === 1) return alert('At least one review is required.');
        button.closest('.review-item').remove(); renumber();
    });
})();
</script>
@endpush
@endif

@push('scripts')
<script>
const addReviewImage = document.getElementById('addReviewImage');
if (addReviewImage) {
    const cards = [...document.querySelectorAll('.review-upload-card')];
    cards.forEach(card => { card.hidden = card.dataset.saved !== 'true' && card.dataset.invalid !== 'true'; });
    if (cards.every(card => card.hidden)) cards[0].hidden = false;
    let nextIndex = Math.max(0, ...cards.map(card => Number(card.querySelector('input[type=file]').name.replace('review_image_', '')))) + 1;
    addReviewImage.hidden = false;
    addReviewImage.addEventListener('click', () => {
        let card = cards.find(card => card.hidden);
        if (!card) {
            const template = document.createElement('template');
            template.innerHTML = document.getElementById('reviewImageTemplate').innerHTML.replaceAll('__INDEX__', String(nextIndex++));
            card = template.content.firstElementChild;
            document.querySelector('.review-upload-grid').appendChild(card);
            cards.push(card);
        }
        card.hidden = false;
        card.querySelector('input[type=file]').focus();
    });
}
document.addEventListener('change', event => {
    const input = event.target;
    if (!input.matches('[data-image-preview]')) return;
    const file = input.files[0];
    if (!file) return;
    const removal = document.querySelector(`[name="remove_${input.name}"]`);
    if (removal) removal.checked = false;
    const preview = document.getElementById(input.dataset.imagePreview);
    const reader = new FileReader();
    reader.onload = () => { preview.src = reader.result; preview.hidden = false; };
    reader.readAsDataURL(file);
});
</script>
@endpush
