<div class="field">
    <label for="video_url">YouTube / Video link — ভিডিও লিংক</label>
            <input id="video_url" name="video_url" type="url" maxlength="2048" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('video_url', data_get($section->content, 'video_url')) }}" aria-describedby="video-url-help">
            <small id="video-url-help">YouTube (Shorts সহ) অথবা সরাসরি MP4, WebM বা MOV ভিডিও লিংক দিন। লিংক থাকলে সেটিই দেখাবে; খালি রাখলে আপলোড করা ভিডিও চলবে। MOV চালানো ব্রাউজারের সাপোর্টের ওপর নির্ভর করে।</small>
            @php
                $savedVideo = \App\Support\VideoSource::fromUrl(data_get($section->content, 'video_url'));
                if (!$savedVideo && data_get($section->content, 'video_file')) {
                    $savedVideo = ['type' => 'file', 'url' => route('media.show', ['path' => data_get($section->content, 'video_file')])];
                }
            @endphp
            <div style="margin-top:16px">
                @if($savedVideo)
                    <strong>সেভ করা ভিডিওর প্রিভিউ</strong>
                    <div class="saved-video-preview" style="margin-top:10px;aspect-ratio:16/9;max-width:720px;background:#17120e;border-radius:12px;overflow:hidden">
                        @include('landing.video-player', ['source' => $savedVideo, 'title' => data_get($section->content, 'title', 'ভিডিও প্রিভিউ'), 'poster' => null])
                    </div>
                @else
                    <p>এখনও কোনো ভিডিও সেভ করা নেই। উপরে লিংক দিয়ে Save Changes চাপুন।</p>
                @endif
            </div>

    @error('video_url')<small style="color:#dc3545">{{ $message }}</small>@enderror
</div>
