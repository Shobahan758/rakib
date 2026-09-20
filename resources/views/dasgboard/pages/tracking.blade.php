@extends('dasgboard.layouts.app')

@section('title', 'Site Tracking')

@push('styles')
<style>
.provider-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:24px}.provider-card{position:relative;display:flex;min-height:150px;flex-direction:column;padding:20px;border:1px solid var(--line);border-radius:15px;background:#fff;color:var(--ink);text-decoration:none;box-shadow:0 7px 25px #2a180f0b;transition:.2s}.provider-card:hover{transform:translateY(-3px);border-color:#ffc79f;box-shadow:0 13px 30px #2a180f16}.provider-card.active{border-color:var(--primary);box-shadow:0 0 0 2px #ff6b001c,0 13px 30px #2a180f14}.provider-card-icon{display:grid;width:45px;height:45px;margin-bottom:16px;place-items:center;border-radius:12px;background:#fff0e3;color:var(--primary);font-size:21px}.provider-card.meta .provider-card-icon{background:#eef3ff;color:#0866ff}.provider-card.google .provider-card-icon{background:#f1f7ff;color:#4285f4}.provider-card.tiktok .provider-card-icon{background:#f4f4f4;color:#111}.provider-card.other .provider-card-icon{background:#eef9f3;color:#28845a}.provider-card strong{font-size:17px}.provider-card small{margin-top:4px;color:var(--muted)}.provider-status{display:flex;align-items:center;gap:6px;margin-top:auto;padding-top:12px;color:#9a6b4c;font-size:12px;font-weight:700}.provider-status.configured{color:#23804b}.provider-status i{font-size:8px}.tracking-grid{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:22px}.field{margin-bottom:18px}.field label{display:block;margin-bottom:7px;font-weight:700}.field input,.field textarea{width:100%;padding:12px;border:1px solid var(--line);border-radius:9px;font:inherit}.field input:focus,.field textarea:focus{outline:0;border-color:var(--primary);box-shadow:0 0 0 3px #ff6b0014}.field textarea{min-height:150px;resize:vertical}.field small{display:block;margin-top:6px;color:var(--muted)}.form-heading{display:flex;align-items:center;gap:13px;margin-bottom:22px;padding-bottom:17px;border-bottom:1px solid var(--line)}.form-heading .provider-icon{margin:0}.form-heading h2{margin:0;font-size:21px}.form-heading p{margin:3px 0 0;color:var(--muted)}.tracking-actions{display:flex;flex-wrap:wrap;align-items:center;gap:10px}.save,.tracking-check{padding:12px 20px;border:0;border-radius:9px;font:inherit;font-weight:700;cursor:pointer}.save{background:var(--primary);color:#fff}.tracking-check{border:1px solid #efcdb5;background:#fff7f0;color:var(--primary-dark)}.tracking-check:disabled{cursor:wait;opacity:.65}.tracking-check-result{margin-top:14px;padding:12px 14px;border-radius:10px;background:#fff4e9;color:var(--primary-dark);font-weight:600}.tracking-check-result.ready{background:#e8f8ee;color:#237a48}.alert{margin-bottom:18px;padding:12px;border-radius:9px;background:#fff0e3;color:var(--primary-dark)}.tracking-note{color:var(--muted);line-height:1.7}.provider-icon{display:grid;width:52px;height:52px;margin-bottom:14px;place-items:center;border-radius:14px;background:#fff0e3;color:var(--primary);font-size:24px}@media(max-width:1100px){.provider-cards{grid-template-columns:repeat(2,1fr)}}@media(max-width:850px){.tracking-grid{grid-template-columns:1fr}}@media(max-width:560px){.provider-cards{grid-template-columns:1fr}.provider-card{min-height:130px}.tracking-actions>*{width:100%}}
</style>
@endpush

@section('content')
@php
    $config = [
        'meta' => ['Meta Pixel', 'fa-brands fa-meta', ['meta_pixel_id' => ['Meta Pixel ID', 'Paste your real Pixel ID from Meta Events Manager → Data Sources → Settings. Only the ID is needed; do not paste the script.']]],
        'google' => ['Google Tracking', 'fa-brands fa-google', [
            'google_analytics_id' => ['Google Analytics Measurement ID', 'Example: G-XXXXXXXXXX'],
            'google_tag_manager_id' => ['Google Tag Manager Container ID', 'Example: GTM-XXXXXXX'],
            'google_ads_id' => ['Google Ads Conversion ID', 'Example: AW-123456789'],
            'google_ads_conversion_label' => ['Google Ads Purchase Conversion Label', 'Copy the label after AW-123456789/ from your purchase conversion event snippet'],
        ]],
        'tiktok' => ['TikTok Pixel', 'fa-brands fa-tiktok', ['tiktok_pixel_id' => ['TikTok Pixel ID', 'Enter the Pixel ID from TikTok Events Manager']]],
        'other' => ['Other Tracking', 'fa-solid fa-code', [
            'clarity_id' => ['Microsoft Clarity Project ID', 'Example: abc123xyz'],
            'linkedin_partner_id' => ['LinkedIn Partner ID', 'Enter your LinkedIn Insight Tag partner ID'],
            'pinterest_tag_id' => ['Pinterest Tag ID', 'Enter your Pinterest Tag ID'],
            'snapchat_pixel_id' => ['Snapchat Pixel ID', 'Enter your Snapchat Pixel ID'],
            'custom_head_script' => ['Custom Head Script', 'Paste a trusted script to load before the closing head tag'],
            'custom_body_script' => ['Custom Body Script', 'Paste a trusted script to load before the closing body tag'],
        ]],
    ];
    $providerDescriptions = ['meta' => 'Facebook and Instagram', 'google' => 'Analytics, GTM and Ads', 'tiktok' => 'TikTok Events Manager', 'other' => 'Clarity and more platforms'];
    [$title, $icon, $fields] = $config[$provider];
@endphp
<div class="page-heading"><div><h1>Site Tracking</h1><p>Manage every analytics and advertising platform from one place.</p></div></div>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
<div class="provider-cards" aria-label="Tracking platforms">
    @foreach($config as $providerKey => [$providerTitle, $providerIcon, $providerFields])
        @php($isConfigured = collect(array_keys($providerFields))->contains(fn($field) => filled($settings[$field] ?? null)))
        <a class="provider-card {{ $providerKey }} {{ $provider === $providerKey ? 'active' : '' }}" href="{{ route('admin.tracking.edit', $providerKey) }}">
            <span class="provider-card-icon"><i class="{{ $providerIcon }}"></i></span>
            <strong>{{ $providerTitle }}</strong><small>{{ $providerDescriptions[$providerKey] }}</small>
            <span class="provider-status {{ $isConfigured ? 'configured' : '' }}"><i class="fa-solid fa-circle"></i> {{ ($settings[$providerKey.'_enabled'] ?? '1') === '0' ? 'Disabled' : ($providerKey === 'meta' && ($settings['meta_pixel_id'] ?? '') === \App\Models\TrackingSetting::DEMO_META_PIXEL_ID ? 'Demo ID' : ($isConfigured ? 'Configured' : 'Not configured')) }}</span>
        </a>
    @endforeach
</div>
<div class="tracking-grid">
    <form class="panel" method="POST" action="{{ route('admin.tracking.update', $provider) }}">@csrf @method('PUT')
        <div class="form-heading"><span class="provider-icon"><i class="{{ $icon }}"></i></span><div><h2>{{ $title }} Settings</h2><p>Enter the ID provided by {{ $title }}.</p></div></div>
        @if($provider === 'meta')
            <p class="tracking-note">Keep Enable Meta Pixel checked and save your ID. Then open your website from Meta Events Manager → Test Events to check PageView, ViewContent and InitiateCheckout. Purchase is sent only after an order is successfully saved. Saving an ID does not verify that it belongs to your Meta account.</p>
        @endif
        @if($provider === 'meta' && ($settings['meta_pixel_id'] ?? '') === \App\Models\TrackingSetting::DEMO_META_PIXEL_ID)
            <div class="alert" role="status">A demo Pixel ID is set. Visitor Tracking will work, but no data is sent to Meta with this ID. To enable Meta, enter your real Pixel ID from Events Manager and save.</div>
        @endif
        <input type="hidden" name="enabled" value="0">
        <label style="display:block;margin-bottom:20px"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', ($settings[$provider.'_enabled'] ?? '1') !== '0'))> Enable {{ $title }}</label>
        @foreach($fields as $name => [$label, $help])
            <div class="field"><label for="{{ $name }}">{{ $label }}</label>
                @if(str_contains($name, 'script'))
                    <textarea id="{{ $name }}" name="{{ $name }}" spellcheck="false">{{ old($name, $settings[$name] ?? '') }}</textarea>
                @else
                    <input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $settings[$name] ?? '') }}" autocomplete="off">
                @endif
                <small>{{ $help }}</small>@error($name)<small style="color:#dc3545">{{ $message }}</small>@enderror
            </div>
        @endforeach
        <div class="tracking-actions">
            <button class="save" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Tracking Settings</button>
            <button class="tracking-check" id="trackingCheck" type="button"
                data-check-url="{{ route('admin.tracking.check', $provider) }}"><i class="fa-solid fa-circle-check"></i>
                Check Installation</button>
        </div>
        <p class="tracking-check-result" id="trackingCheckResult" role="status" aria-live="polite" hidden></p>
    </form>
    <aside class="panel tracking-note"><span class="provider-icon"><i class="{{ $icon }}"></i></span><h2>Automatic Installation</h2><p>Save the ID supplied by the platform. The required tracking code will be added automatically to every landing-page visit.</p><p>Meta, GA4 and TikTok receive product views, checkout starts and saved order conversions. Google Ads purchase conversions require both an ID and conversion label.</p>@if($provider === 'google')<p>For GTM, create Custom Event triggers for <code>store_view_item</code>, <code>store_begin_checkout</code> and <code>store_purchase</code>. Product/order data is available under <code>ecommerce</code>. Choose direct GA4 or GTM for the same property to avoid duplicate reporting.</p>@endif<p>Configured means the settings are saved. Check delivery using the provider’s Test Events or Realtime screen. A purchase here means a saved cash-on-delivery order, not collected payment.</p><a href="{{ route('home') }}" target="_blank" rel="noopener">Open website to verify tracking</a></aside>
</div>
@endsection

@push('scripts')
<script src="{{ asset('asset/js/tracking-admin.js') }}?v={{ filemtime(public_path('asset/js/tracking-admin.js')) }}"></script>
@endpush
