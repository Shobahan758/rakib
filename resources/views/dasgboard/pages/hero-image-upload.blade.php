<section class="panel review-upload-panel">
    <h2>Hero Slider Images</h2>
    <p>ছবির সংখ্যার নির্দিষ্ট সীমা নেই। “আরও ছবি যোগ করুন” চাপুন, ছবি ও Alt text দিন, তারপর সেভ করুন। একসঙ্গে upload limit হলে কয়েকবারে সেভ করতে পারবেন।</p>
    @php
        $heroImageKeys = array_unique(array_merge(
            ['image_1', 'image_2'],
            array_filter(array_keys($section->content ?? []), fn ($key) => preg_match('/^image_[1-9][0-9]*$/D', $key)),
            array_map(fn ($key) => substr($key, 0, -4), array_filter(array_keys(session()->getOldInput()), fn ($key) => preg_match('/^image_[1-9][0-9]*_alt$/D', $key))),
        ));
        natsort($heroImageKeys);
        $nextHeroImageIndex = max(array_map(fn ($key) => (int) substr($key, 6), $heroImageKeys)) + 1;
    @endphp
    <div class="review-upload-grid" id="heroImageList">
        @foreach($heroImageKeys as $imageKey)
            @include('dasgboard.pages.hero-image-card', ['i' => (int) substr($imageKey, 6)])
        @endforeach
    </div>
    <button class="add-review" id="addHeroImage" type="button" hidden>＋ আরও ছবি যোগ করুন</button>
    <template id="heroImageTemplate">
        @include('dasgboard.pages.hero-image-card', ['imageKey' => 'image___INDEX__', 'i' => '__INDEX__'])
    </template>
    <p>মূল Hero image ডান পাশের “Section image” field থেকে পরিবর্তন করুন। এখানে যোগ করা প্রতিটি ছবি তার পরে slide হবে।</p>
    <button class="save" type="submit"><i class="fa-solid fa-floppy-disk"></i> Hero slider সেভ করুন</button>
</section>

@push('scripts')
<script>
(() => {
    const button = document.getElementById('addHeroImage');
    const list = document.getElementById('heroImageList');
    if (!button || !list) return;
    let nextIndex = {{ $nextHeroImageIndex }};
    button.hidden = false;
    button.addEventListener('click', () => {
        const template = document.createElement('template');
        template.innerHTML = document.getElementById('heroImageTemplate').innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        const card = template.content.firstElementChild;
        list.appendChild(card);
        card.querySelector('input[type=file]').focus();
    });
})();
</script>
@endpush
