@php
    $orderProductOptions = \App\Models\Product::orderBy('sort_order')->get()->mapWithKeys(fn ($product) => [$product->name.' — ৳'.$product->price => $product->price]);
    $currentProductLabel = old('burger_type', $order->burger_type ?? '');
    if ($currentProductLabel && !$orderProductOptions->has($currentProductLabel)) $orderProductOptions->put($currentProductLabel, $order->unit_price ?? 0);
@endphp
<label for="burger_type" style="display:block;margin-bottom:6px;font-weight:600">Product / পণ্য</label>
<select id="burger_type" name="burger_type" style="width:100%;padding:11px 12px;border:1px solid var(--line);border-radius:9px;font:inherit" onchange="if(this.selectedOptions[0].dataset.price!==undefined)document.getElementById('unit_price').value=this.selectedOptions[0].dataset.price">
    <option value="">Select a product</option>
    @foreach($orderProductOptions as $label => $price)
        <option value="{{ $label }}" data-price="{{ $price }}" @selected($currentProductLabel === $label)>{{ $label }}</option>
    @endforeach
</select>
@error('burger_type')<small style="color:#dc3545">{{ $message }}</small>@enderror
