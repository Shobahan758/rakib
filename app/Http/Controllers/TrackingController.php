<?php

namespace App\Http\Controllers;

use App\Models\TrackingSetting;
use App\Models\SiteVisit;
use App\Models\TrackingEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingController extends Controller
{
    private const PROVIDERS = ['meta', 'google', 'tiktok', 'other'];

    public function edit(Request $request, string $provider = 'visitors'): View|JsonResponse
    {
        if ($provider === 'visitors') {
            $period = $request->validate(['period' => ['nullable', 'in:today,7,30,all']])['period'] ?? 'all';
            $events = TrackingEvent::query();
            if ($period !== 'all') $events->whereDate('occurred_on', '>=', today()->subDays($period === 'today' ? 0 : (int) $period - 1));
            $dailyVisitors = SiteVisit::query()
                ->selectRaw('visited_on, COUNT(DISTINCT visitor_hash) as visitors')
                ->groupBy('visited_on')
                ->orderByDesc('visited_on')
                ->limit(30)
                ->get();

            $report = [
                'totalVisitors' => SiteVisit::query()->distinct()->count('visitor_hash'),
                'todayVisitors' => SiteVisit::whereDate('visited_on', today())->distinct()->count('visitor_hash'),
                'sevenDayVisitors' => SiteVisit::whereDate('visited_on', '>=', today()->subDays(6))->distinct()->count('visitor_hash'),
                'thirtyDayVisitors' => SiteVisit::whereDate('visited_on', '>=', today()->subDays(29))->distinct()->count('visitor_hash'),
                'dailyVisitors' => $dailyVisitors,
                'period' => $period,
                'eventCounts' => $events->selectRaw('event_name, COUNT(*) as total')->groupBy('event_name')->pluck('total', 'event_name'),
            ];
            return $request->expectsJson()
                ? response()->json($report)->header('Cache-Control', 'no-store')
                : view('dasgboard.pages.visitor-tracking', $report);
        }

        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return view('dasgboard.pages.tracking', [
            'provider' => $provider,
            'settings' => TrackingSetting::values(),
        ]);
    }

    public function collect(Request $request): JsonResponse
    {
        $validated = $request->validate(['event' => ['required', 'string', 'in:'.implode(',', array_diff(TrackingEvent::TYPES, ['order_completed']))], 'path' => ['nullable', 'string', 'max:255', 'starts_with:/']]);

        TrackingEvent::create([
            'event_name' => $validated['event'],
            'visitor_hash' => hash('sha256', $request->ip().'|'.$request->userAgent()),
            'path' => mb_substr((string) $request->input('path', '/'), 0, 255),
            'occurred_on' => today()->toDateString(),
        ]);

        return response()->json(['recorded' => true], 201);
    }

    public function check(string $provider): JsonResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        $settings = TrackingSetting::values();
        $enabled = ($settings[$provider.'_enabled'] ?? '1') !== '0';
        $ready = match ($provider) {
            'meta' => filled($settings['meta_pixel_id'] ?? null)
                && $settings['meta_pixel_id'] !== TrackingSetting::DEMO_META_PIXEL_ID,
            'google' => filled($settings['google_analytics_id'] ?? null)
                || filled($settings['google_tag_manager_id'] ?? null)
                || (filled($settings['google_ads_id'] ?? null) && filled($settings['google_ads_conversion_label'] ?? null)),
            'tiktok' => filled($settings['tiktok_pixel_id'] ?? null),
            'other' => collect(TrackingSetting::PROVIDER_FIELDS['other'])
                ->contains(fn ($field) => filled($settings[$field] ?? null)),
        };

        return response()->json([
            'enabled' => $enabled,
            'ready' => $enabled && $ready,
            'message' => ! $enabled
                ? 'Tracking বন্ধ আছে। Enable করে Save Tracking Settings চাপুন।'
                : ($ready
                    ? 'সেটআপ সক্রিয়। Tracking code landing page-এ স্বয়ংক্রিয়ভাবে যুক্ত হচ্ছে। এখন provider-এর Test Events বা Realtime screen-এ delivery যাচাই করুন।'
                    : 'প্রয়োজনীয় ID এখনো দেওয়া হয়নি। সঠিক ID দিয়ে Save Tracking Settings চাপুন।'),
            'landing_url' => route('home'),
        ])->header('Cache-Control', 'no-store');
    }

    public function update(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        $fields = TrackingSetting::PROVIDER_FIELDS[$provider];
        if ($provider === 'meta' && is_string($request->input('meta_pixel_id'))) {
            $request->merge(['meta_pixel_id' => trim(strtr($request->input('meta_pixel_id'), array_combine(
                preg_split('//u', '০১২৩৪৫৬৭৮৯', -1, PREG_SPLIT_NO_EMPTY),
                range(0, 9),
            )))]);
        }
        $formats = [
            'meta_pixel_id' => '/^(?!0+$)[0-9]{5,30}$/D',
            'google_analytics_id' => '/^G-[A-Z0-9]+$/',
            'google_tag_manager_id' => '/^GTM-[A-Z0-9]+$/',
            'google_ads_id' => '/^AW-\d+$/',
            'google_ads_conversion_label' => '/^[A-Za-z0-9_-]+$/',
            'tiktok_pixel_id' => '/^[A-Za-z0-9]{5,50}$/',
            'clarity_id' => '/^[a-zA-Z0-9]+$/',
            'linkedin_partner_id' => '/^\d+$/',
            'pinterest_tag_id' => '/^\d+$/',
            'snapchat_pixel_id' => '/^[a-fA-F0-9-]{32,36}$/',
        ];
        $rules = ['enabled' => ['nullable', 'boolean']];
        foreach ($fields as $field) {
            $rules[$field] = str_contains($field, 'script')
                ? ['nullable', 'string', 'max:10000']
                : ['nullable', 'string', 'max:100', 'regex:'.$formats[$field]];
        }
        if ($provider === 'google') $rules['google_ads_id'][] = 'required_with:google_ads_conversion_label';
        if ($provider === 'meta' && $request->boolean('enabled', true)) $rules['meta_pixel_id'][] = 'required';
        $validated = $request->validate($rules, [
            'meta_pixel_id.required' => 'Enter your real Meta Pixel ID to enable tracking.',
            'meta_pixel_id.regex' => 'Copy the numeric Pixel ID from Meta Events Manager. A demo ID or tracking script cannot be used.',
        ]);
        TrackingSetting::updateOrCreate(['key' => $provider.'_enabled'], ['value' => $request->boolean('enabled', true) ? '1' : '0']);

        foreach ($fields as $field) {
            TrackingSetting::updateOrCreate(['key' => $field], ['value' => trim((string) ($validated[$field] ?? '')) ?: null]);
        }

        return back()->with('success', ucfirst($provider).' tracking settings saved successfully.');
    }
}
