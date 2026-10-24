@extends('dasgboard.layouts.app')
@section('title', 'Book Courier')
@section('content')
<div class="page-heading"><div><h1>Book Courier — #{{ $order->id }}</h1><p>{{ $order->name }} · {{ $order->phone }}</p></div><a class="btn" href="{{ route('admin.orders.index') }}">Back to Orders</a></div>
@include('dasgboard.pages.couriers.messages')
<section class="panel courier-book" style="max-width:720px">
<p>{{ $order->address }}</p>
<p><strong>Cash on delivery: ৳{{ number_format($cod) }}</strong> (একই ডেলিভারির অতিরিক্ত পণ্যসহ)</p>
@if($booking)
<p>Booking: <strong>{{ ucfirst($booking->status) }}</strong> · {{ ucfirst($booking->provider) }} @if($booking->consignment_id) · Consignment: {{ $booking->consignment_id }} @endif @if($booking->tracking_code) · Tracking: {{ $booking->tracking_code }} @endif</p>
@if($booking->message)<p role="alert">{{ $booking->message }}</p>@endif
@endif
@if($booking && in_array($booking->status, ['booked', 'submitting', 'unknown']))
<p>এই অর্ডারের বুকিং ইতিমধ্যে পাঠানো হয়েছে। নতুন করে বুকিংয়ের আগে কুরিয়ার মার্চেন্ট অ্যাকাউন্টে যাচাই করুন।</p>
@elseif($order->parent_order_id || !in_array($order->status, ['pending','processing']))
<p>শুধু Pending মূল অর্ডার থেকে বুকিং করুন।</p>
@elseif($settings->isEmpty())
<p>কোনো কুরিয়ার কনফিগার করা নেই। Super Admin থেকে Courier Settings-এ তথ্য সংরক্ষণ করুন।</p>
@else
<form method="POST" action="{{ route('admin.couriers.store', $order) }}" id="courier-book-form">
@csrf
<label for="provider">Courier</label>
<select name="provider" id="provider">@foreach($settings as $setting)<option value="{{ $setting->provider }}" @selected(old('provider') === $setting->provider)>{{ ucfirst($setting->provider) }} ({{ $setting->credentials['environment'] ?? 'live' }})</option>@endforeach</select>
<div data-provider="pathao redx">
<label for="weight">Parcel weight (kg)</label><input id="weight" name="weight" type="number" min="0.5" max="100" step="0.01" value="{{ old('weight', '0.5') }}">
</div>
<div data-provider="pathao redx">
<button type="button" class="btn" id="load-locations">Load delivery locations</button>
<p id="location-message" role="status"></p>
<label for="location-city">City / RedX area</label><select id="location-city"><option value="">Choose location</option></select>
<div id="pathao-location-selects"><label for="location-zone">Zone</label><select id="location-zone"><option value="">Choose zone</option></select><label for="location-area">Area (optional)</label><select id="location-area"><option value="">Choose area</option></select></div>
</div>
<div data-provider="pathao">
<p>কুরিয়ারের তালিকা থেকে সঠিক City / Zone / Area বেছে নিন।</p>
<label for="city_id">Recipient City ID</label><input id="city_id" name="city_id" type="number" min="1" value="{{ old('city_id') }}">
<label for="zone_id">Recipient Zone ID</label><input id="zone_id" name="zone_id" type="number" min="1" value="{{ old('zone_id') }}">
</div>
<div data-provider="pathao redx">
<label for="area_id">Delivery Area ID (required for RedX)</label><input id="area_id" name="area_id" type="number" min="1" value="{{ old('area_id') }}">
</div>
<div data-provider="redx">
<label for="area_name">RedX delivery area name</label><input id="area_name" name="area_name" maxlength="100" value="{{ old('area_name') }}">
</div>
<label for="note">Delivery instruction (optional)</label><textarea name="note" id="note" maxlength="500">{{ old('note') }}</textarea>
<p>বুক করলে কুরিয়ারে ডেলিভারি তৈরি হবে এবং অর্ডার Shipping হবে।</p>
<button class="btn" type="submit">Book Courier — ৳{{ number_format($cod) }}</button>
</form>
@endif
</section>
@endsection
@push('styles')
<style>.courier-book label{display:block;margin:14px 0 6px}.courier-book input,.courier-book select,.courier-book textarea{width:100%;padding:12px;border:1px solid #e4d9d0;border-radius:9px;font:inherit;background:#fff}.courier-message{padding:14px;margin-bottom:15px;border:1px solid #f0d6c3;border-radius:9px;background:#fff}.courier-error{color:#a11}.courier-book .btn{margin-top:15px;cursor:pointer}[hidden]{display:none!important}</style>
@endpush
@push('scripts')
<script>
(() => {
 const form = document.getElementById('courier-book-form');
 if (!form) return;
 const provider = document.getElementById('provider');
 const update = () => form.querySelectorAll('[data-provider]').forEach(group => {
   const active = group.dataset.provider.split(' ').includes(provider.value);
   group.hidden = !active;
   group.querySelectorAll('input').forEach(input => { input.disabled = !active; });
 });
 const city = document.getElementById('location-city'), zone = document.getElementById('location-zone'), area = document.getElementById('location-area');
 const message = document.getElementById('location-message');
 let generation = 0;
 const reset = select => { select.replaceChildren(new Option('Choose location', '')); };
 const load = async (select, params = {}) => {
   const version = ++generation;
   reset(select); message.textContent = 'Loading…';
   const url = new URL(@json(route('admin.couriers.locations')), window.location.origin);
   url.searchParams.set('provider', provider.value);
   Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, value));
   try {
     const response = await fetch(url, {headers: {'Accept': 'application/json'}});
     const data = await response.json();
     if (version !== generation) return;
     if (!response.ok) throw new Error();
     data.locations.forEach(location => select.add(new Option(location.name, location.id)));
     message.textContent = 'Choose the delivery location below.';
   } catch (_) { if (version === generation) message.textContent = 'Could not load locations. Test the courier connection or enter the IDs below.'; }
 };
 document.getElementById('load-locations').addEventListener('click', () => load(city));
 city.addEventListener('change', () => {
   generation++; reset(zone); reset(area);
   document.getElementById('zone_id').value = ''; document.getElementById('area_id').value = '';
   if (provider.value === 'redx') {
     document.getElementById('area_id').value = city.value;
     document.getElementById('area_name').value = city.value ? city.selectedOptions[0].text : '';
   } else {
     document.getElementById('city_id').value = city.value;
     if (city.value) load(zone, {city: city.value});
   }
 });
 zone.addEventListener('change', () => {
   generation++; reset(area); document.getElementById('area_id').value = '';
   document.getElementById('zone_id').value = zone.value;
   if (zone.value) load(area, {zone: zone.value});
 });
 area.addEventListener('change', () => { document.getElementById('area_id').value = area.value; });
 provider.addEventListener('change', () => {
   generation++; [city, zone, area].forEach(reset); message.textContent = '';
   ['city_id','zone_id','area_id','area_name'].forEach(id => { document.getElementById(id).value = ''; });
   document.getElementById('pathao-location-selects').hidden = provider.value !== 'pathao'; update();
 });
 document.getElementById('pathao-location-selects').hidden = provider.value !== 'pathao'; update();
 form.addEventListener('submit', () => { const button = form.querySelector('button[type="submit"]'); button.disabled = true; button.textContent = 'Booking…'; });
})();
</script>
@endpush
