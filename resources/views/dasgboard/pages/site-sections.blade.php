@extends('dasgboard.layouts.app')

@section('title', 'All Site Content & Sections')

@push('styles')
<style>
    .section-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.section-toggle{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:17px 18px;border:1px solid var(--line);border-radius:13px;background:#fff}.section-copy strong{display:block;font-size:17px}.section-copy small{color:var(--muted)}.switch{position:relative;flex:0 0 auto;width:52px;height:29px}.switch input{position:absolute;opacity:0}.slider{position:absolute;inset:0;border-radius:99px;background:#cfc4bc;cursor:pointer;transition:.2s}.slider:before{content:'';position:absolute;width:21px;height:21px;left:4px;top:4px;border-radius:50%;background:#fff;box-shadow:0 2px 7px #0003;transition:.2s}.switch input:checked+.slider{background:var(--primary)}.switch input:checked+.slider:before{transform:translateX(23px)}.toolbar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}.bulk-buttons{display:flex;gap:8px}.bulk-buttons button{padding:8px 12px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--ink);cursor:pointer;font:inherit}.save-status{margin-top:20px;padding:12px 20px;border:0;border-radius:9px;background:var(--primary);color:#fff;font:inherit;font-weight:700;cursor:pointer}.alert{margin-bottom:18px;padding:12px;border-radius:9px;background:#fff0e3;color:var(--primary-dark)}@media(max-width:720px){.section-list{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="page-heading"><div><h1>All Site Content & Sections</h1><p>সাইটের সব লেখা, ছবি, ভিডিও, বাটন, রং ও section settings এখান থেকে পরিবর্তন করুন।</p></div></div>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@include('dasgboard.pages.products.settings-links')
<form method="POST" action="{{ route('admin.landing.visibility') }}">
    @csrf @method('PUT')
    <section class="panel">
        <div class="toolbar"><strong>{{ count($definitions) }} settings & sections total</strong><div class="bulk-buttons"><button id="enableAll" type="button">Enable All Sections</button><button id="disableAll" type="button">Disable All Sections</button></div></div>
        <div class="section-list">
            @foreach($definitions as $slug => $definition)
                @php($canToggle = !in_array($slug, ['site', 'social', 'seo'], true))
                @php($isVisible = $sections->get($slug)?->is_visible ?? true)
                <div class="section-toggle">
                    <span class="section-copy"><strong>{{ $definition['label'] }}</strong><small class="status-text">{{ $canToggle ? ($isVisible ? 'Enabled' : 'Disabled') : 'Always active settings' }}</small></span>
                    <a href="{{ route('admin.landing.edit', $slug) }}" style="color:var(--primary);white-space:nowrap">Edit all content</a>
                    @if($canToggle)
                        <label class="switch"><input aria-label="{{ $definition['label'] }} visibility" class="section-checkbox" type="checkbox" name="active_sections[]" value="{{ $slug }}" @checked($isVisible)><span class="slider"></span></label>
                    @endif
                </div>
            @endforeach
        </div>
        <button class="save-status" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Status</button>
    </section>
</form>
@endsection

@push('scripts')
<script>
    const sectionCheckboxes = document.querySelectorAll('.section-checkbox');
    const updateStatusText = item => item.closest('.section-toggle').querySelector('.status-text').textContent = item.checked ? 'Enabled' : 'Disabled';
    sectionCheckboxes.forEach(item => item.addEventListener('change', () => updateStatusText(item)));
    document.getElementById('enableAll')?.addEventListener('click', () => sectionCheckboxes.forEach(item => { item.checked = true; updateStatusText(item); }));
    document.getElementById('disableAll')?.addEventListener('click', () => sectionCheckboxes.forEach(item => { item.checked = false; updateStatusText(item); }));
</script>
@endpush
