@extends('dasgboard.layouts.app')
@section('title', 'Courier Settings')
@section('content')
<div class="page-heading"><div><h1>Courier Settings</h1><p>আপনার ব্যবহৃত কুরিয়ারের তথ্য সংরক্ষণ করুন, তারপর Test Connection দিন।</p></div><a class="btn" href="{{ route('admin.orders.index') }}">Open Orders</a></div>
@include('dasgboard.pages.couriers.messages')
<form method="POST" action="{{ route('admin.couriers.save') }}" autocomplete="off">
@csrf @method('PUT')
<div class="courier-grid">
@foreach(\App\Models\CourierSetting::FIELDS as $provider => $fields)
<section class="panel">
<h2>{{ $provider === 'redx' ? 'RedX' : ucfirst($provider) }} <small>{{ ($settings[$provider] ?? null)?->configured() ? 'Configured' : 'Not Configured' }}</small></h2>
<p>খালি রাখলে আগে সংরক্ষিত তথ্য থাকবে। সংরক্ষণ করলে কোনো ডেলিভারি তৈরি হবে না।</p>
@if($provider !== 'steadfast')
<label for="{{ $provider }}-environment">Environment</label>
<select id="{{ $provider }}-environment" name="{{ $provider }}[environment]">
<option value="live" @selected(($settings[$provider]->credentials['environment'] ?? 'live') === 'live')>Live merchant account</option>
<option value="sandbox" @selected(($settings[$provider]->credentials['environment'] ?? 'live') === 'sandbox')>Sandbox / Test account</option>
</select>
@endif
@foreach($fields as $field => $label)
<label for="{{ $provider }}-{{ $field }}">{{ $label }}</label>
<input id="{{ $provider }}-{{ $field }}" name="{{ $provider }}[{{ $field }}]" type="password" autocomplete="new-password" placeholder="Enter or replace {{ $label }}" maxlength="2000">
@endforeach
<button class="btn" type="submit" form="test-{{ $provider }}">Test Connection (saved credentials)</button>
</section>
@endforeach
</div>
<button class="btn" type="submit">Save Courier Settings</button>
</form>
@foreach(\App\Models\CourierSetting::FIELDS as $provider => $fields)
<form id="test-{{ $provider }}" method="POST" action="{{ route('admin.couriers.test', $provider) }}">@csrf</form>
@endforeach
@endsection
@push('styles')
<style>
.courier-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-bottom:20px}.courier-grid .panel{align-self:start}.courier-grid label,.courier-book label{display:block;margin:14px 0 6px}.courier-grid input,.courier-grid select,.courier-book input,.courier-book select,.courier-book textarea{display:block;width:100%;padding:12px;border:1px solid #e4d9d0;border-radius:9px;background:#fff;color:#222;font:inherit;margin-bottom:15px}.courier-grid h2 small{display:block;font-size:.8rem;color:#76665d}.courier-message{padding:14px;margin-bottom:15px;border:1px solid #f0d6c3;border-radius:9px;background:#fff}.courier-error{color:#a11}.courier-grid .btn,.courier-book .btn{cursor:pointer;margin-top:8px}@media(max-width:767px){.courier-grid{grid-template-columns:1fr}}
</style>
@endpush
