<section class="panel review-upload-panel">
    <h2>গ্যালারির ছবি</h2>
    <p>ছবির সংখ্যার নির্দিষ্ট সীমা নেই। আরও ছবি যোগ করে সেভ করুন। বেশি ছবি হলে কয়েকবারে আপলোড করতে পারবেন। প্রতিটি ছবি সর্বোচ্চ ৪ MB।</p>
    @php
        $galleryKeys = array_unique(array_merge(
            array_map(fn ($i) => "image_{$i}", range(1, 6)),
            array_filter(array_keys($section->content ?? []), fn ($key) => preg_match('/^image_[1-9][0-9]*$/D', $key)),
            array_map(fn ($key) => substr($key, 0, -4), array_filter(array_keys(session()->getOldInput()), fn ($key) => preg_match('/^image_[1-9][0-9]*_alt$/D', $key))),
        ));
        natsort($galleryKeys);
        $nextGalleryIndex = max(array_map(fn ($key) => (int) substr($key, 6), $galleryKeys)) + 1;
        $galleryFallbacks = array_fill(0, 6, 'furniture-polish-combo.webp');
    @endphp
    <div class="review-upload-grid" id="galleryImageList">
        @foreach($galleryKeys as $imageKey)
            @continue(array_key_exists($imageKey, $section->content) && $section->content[$imageKey] === null && !session()->hasOldInput($imageKey.'_alt'))
            @include('dasgboard.pages.gallery-image-card', ['i' => (int) substr($imageKey, 6)])
        @endforeach
    </div>
    <button class="add-review" id="addGalleryImage" type="button" hidden>＋ আরও ছবি যোগ করুন</button>
    <template id="galleryImageTemplate">
        @include('dasgboard.pages.gallery-image-card', ['imageKey' => 'image___INDEX__', 'i' => '__INDEX__'])
    </template>
    <button class="save" type="submit">গ্যালারির ছবি ও সেটিংস সেভ করুন</button>
</section>
@push('scripts')
<script>
(() => {
    const button = document.getElementById('addGalleryImage');
    const list = document.getElementById('galleryImageList');
    let nextIndex = {{ $nextGalleryIndex }};
    button.hidden = false;
    button.addEventListener('click', () => {
        const template = document.createElement('template');
        template.innerHTML = document.getElementById('galleryImageTemplate').innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        const card = template.content.firstElementChild;
        list.appendChild(card);
        card.querySelector('input[type=file]').focus();
    });
})();
</script>
@endpush
