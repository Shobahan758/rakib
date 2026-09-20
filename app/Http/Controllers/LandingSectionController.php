<?php

namespace App\Http\Controllers;

use App\Models\LandingSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LandingSectionController extends Controller
{
    public function index(): View
    {
        $definitions = LandingSection::definitions();
        $sections = LandingSection::whereIn('slug', array_keys($definitions))->get()->keyBy('slug');

        return view('dasgboard.pages.site-sections', compact('definitions', 'sections'));
    }

    public function updateVisibility(Request $request): RedirectResponse
    {
        $slugs = array_values(array_diff(array_keys(LandingSection::definitions()), ['site', 'social', 'seo']));
        $validated = $request->validate([
            'active_sections' => ['nullable', 'array'],
            'active_sections.*' => ['string', 'in:'.implode(',', $slugs)],
        ]);
        $active = $validated['active_sections'] ?? [];

        foreach ($slugs as $slug) {
            LandingSection::updateOrCreate(
                ['slug' => $slug],
                ['content' => LandingSection::where('slug', $slug)->value('content') ?? LandingSection::defaults($slug), 'is_visible' => in_array($slug, $active, true)],
            );
        }

        return back()->with('success', 'Section visibility updated successfully.');
    }

    public function edit(string $section): View
    {
        $definition = LandingSection::definitions()[$section] ?? abort(404);

        $record = LandingSection::firstOrNew(['slug' => $section]);
        $record->content = array_merge(LandingSection::defaults($section), $record->content ?? []);
        if ($definition['dynamic_reviews'] ?? false) $record->content = array_merge($record->content, ['reviews' => LandingSection::reviewItems($record->content)]);

        return view('dasgboard.pages.landing-section', ['slug' => $section, 'definition' => $definition, 'section' => $record]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $definition = LandingSection::definitions()[$section] ?? abort(404);
        $rules = ['is_visible' => ['nullable', 'boolean']];
        $imageFields = $section === 'order' ? ['modal_image'] : [];
        if ($definition['image'] ?? false) $imageFields[] = 'image';
        if (! in_array($section, ['site', 'social', 'seo'], true)) $imageFields = array_merge($imageFields, ['background_image', 'mobile_background_image']);
        if ($section === 'site') $imageFields[] = 'favicon';
        for ($i = 1; $i <= ($definition['images'] ?? 0); $i++) $imageFields[] = "image_{$i}";
        if ($definition['review_images'] ?? false) {
            foreach (array_keys($request->all()) as $key) {
                if (preg_match('/^(?:remove_)?(review_image_[1-9][0-9]*)$/D', $key, $match)) $imageFields[] = $match[1];
            }
            $imageFields = array_values(array_unique($imageFields));
        }
        if (in_array($section, ['gallery', 'hero'], true)) {
            foreach (array_keys($request->all()) as $key) {
                if (preg_match('/^(?:remove_)?(image_[1-9][0-9]*)(?:_alt)?$/D', $key, $match)) {
                    $imageFields[] = $match[1];
                    $rules[$match[1].'_alt'] = ['nullable', 'string', 'max:1000'];
                }
            }
            $imageFields = array_values(array_unique($imageFields));
        }
        foreach ($imageFields as $key) {
            $rules[$key] = ['nullable', 'image'];
            if (!str_starts_with($key, 'review_image_')) $rules[$key][] = 'max:4096';
            $rules["remove_{$key}"] = ['nullable', 'boolean'];
        }
        if ($definition['video'] ?? false) {
            $rules['video_file'] = ['nullable', 'file', 'mimes:mp4,mov,webm,qt', 'max:51200'];
            $rules['remove_video_file'] = ['nullable', 'boolean'];
        }
        for ($i = 1; $i <= ($definition['images'] ?? 0); $i++) $rules["image_{$i}"] = ['nullable', 'image', 'max:4096'];
        foreach ($definition['fields'] as $key => [, $type]) {
            $rules[$key] = $type === 'number' ? ['nullable', 'numeric', 'min:0', 'max:99999999'] : ['nullable', 'string', 'max:10000'];
            if ($type === 'number' && array_key_exists($key, LandingSection::layoutFields())) $rules[$key] = ['nullable', 'integer', 'min:0', 'max:3000'];
            if ($key === 'section_line_height') $rules[$key] = ['nullable', 'numeric', 'min:1.2', 'max:3'];
            if (in_array($key, ['button_radius', 'section_button_radius', 'section_image_radius'], true)) $rules[$key] = ['nullable', 'integer', 'min:0', 'max:100'];
            if ($key === 'logo_width') $rules[$key] = ['nullable', 'integer', 'min:32', 'max:400'];
            if ($type === 'color') $rules[$key] = ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'];
            if ($type === 'select') $rules[$key] = ['nullable', \Illuminate\Validation\Rule::in(array_keys(LandingSection::selectOptions()[$key] ?? []))];
            if (in_array($key, ['base_font_size', 'mobile_font_size', 'section_text_size', 'section_mobile_text_size'], true)) $rules[$key] = ['nullable', 'integer', 'min:12', 'max:32'];
            if (in_array($key, ['section_heading_size', 'section_mobile_heading_size'], true)) $rules[$key] = ['nullable', 'integer', 'min:16', 'max:120'];
            if ($key === 'slider_interval') $rules[$key] = ['nullable', 'integer', 'min:2', 'max:60'];
            if (in_array($key, ['button_link', 'mobile_order_link', 'whatsapp_link'], true)) $rules[$key] = ['nullable', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (preg_match('~^(?:#[a-zA-Z][a-zA-Z0-9_:-]*|/(?!/)[^\s]*)$~', $value)) return;
                if (filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)) return;
                $fail('একটি সঠিক https:// লিংক, /path অথবা #section দিন।');
            }];
            if (in_array($key, ['inside_delivery_charge', 'outside_delivery_charge'], true)) $rules[$key] = ['nullable', 'integer', 'min:0', 'max:99999999'];
        }
        if ($definition['video'] ?? false) {
            $rules['video_url'] = ['nullable', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (! \App\Support\VideoSource::fromUrl($value)) {
                    $fail('সঠিক YouTube লিংক অথবা সরাসরি MP4, WebM বা MOV ভিডিও লিংক দিন।');
                }
            }];
        }
        if ($section === 'video_reviews') {
            $rules['video_links'] = ['nullable', 'string', 'max:20000', function ($attribute, $value, $fail) {
                $links = preg_split('/\R/u', trim($value), -1, PREG_SPLIT_NO_EMPTY);
                if (count($links) > 20) { $fail('সর্বোচ্চ ২০টি ভিডিও লিংক দিন।'); return; }
                foreach ($links as $index => $link) {
                    if (! \App\Support\VideoSource::fromUrl(trim($link))) {
                        $fail(($index + 1).' নম্বর লাইনে সঠিক YouTube বা সরাসরি ভিডিও লিংক দিন।');
                        return;
                    }
                }
            }];
        }
        if ($definition['dynamic_reviews'] ?? false) {
            $rules += [
                'reviews' => ['required', 'array', 'min:1'],
                'reviews.*.rating' => ['required', 'string', 'max:10'],
                'reviews.*.text' => ['required', 'string', 'max:1000'],
                'reviews.*.avatar' => ['nullable', 'string', 'max:10'],
                'reviews.*.name' => ['required', 'string', 'max:100'],
                'reviews.*.location' => ['nullable', 'string', 'max:100'],
            ];
        }
        $validated = $request->validate($rules, [
            '*.uploaded' => 'ফাইলটি সার্ভারে আপলোড হয়নি। কম ছবি একসঙ্গে দিয়ে আবার চেষ্টা করুন অথবা হোস্টিংয়ের upload_max_filesize ও post_max_size বাড়ান।',
            'video_file.max' => 'The video file must not be larger than 50 MB.',
            'video_file.mimes' => 'The video must be an MP4, WebM, or MOV file.',
        ]);
        $record = LandingSection::firstOrNew(['slug' => $section]);
        $content = $record->content ?? [];
        foreach (array_keys($definition['fields']) as $key) {
            if (array_key_exists($key, $validated)) $content[$key] = $validated[$key] ?? '';
        }
        if ($definition['dynamic_reviews'] ?? false) $content['reviews'] = array_values($validated['reviews']);
        if (in_array($section, ['gallery', 'hero'], true)) {
            foreach ($imageFields as $key) {
                if (array_key_exists($key.'_alt', $validated)) $content[$key.'_alt'] = $validated[$key.'_alt'] ?? '';
            }
        }
        foreach ($imageFields as $key) {
            if ($request->boolean("remove_{$key}")) {
                $this->deleteStoredMedia($content[$key] ?? null);
                if (in_array($section, ['gallery', 'hero'], true) && preg_match('/^image_[1-9][0-9]*$/D', $key)) $content[$key] = null;
                else unset($content[$key]);
            }
            if ($request->hasFile($key)) {
                $newPath = $request->file($key)->store('landing', 'public');
                $this->deleteStoredMedia($content[$key] ?? null);
                $content[$key] = $newPath;
            }
        }
        if (($definition['video'] ?? false) && $request->boolean('remove_video_file')) {
            $this->deleteStoredMedia($content['video_file'] ?? null);
            unset($content['video_file']);
        }
        if (($definition['video'] ?? false) && $request->hasFile('video_file')) {
            $newPath = $request->file('video_file')->store('landing/videos', 'public');
            $this->deleteStoredMedia($content['video_file'] ?? null);
            $content['video_file'] = $newPath;
        }
        $record->fill(['content' => $content, 'is_visible' => in_array($section, ['site', 'social', 'seo'], true) || $request->boolean('is_visible')])->save();

        return back()->with('success', 'Section updated successfully.');
    }

    private function deleteStoredMedia(mixed $path): void
    {
        if (is_string($path) && str_starts_with($path, 'landing/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
