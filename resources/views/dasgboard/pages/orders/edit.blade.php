@extends('dasgboard.layouts.app')

@section('title', 'Edit Order')

@section('content')
    @php
        $backUrl = str_starts_with($returnTo, 'fake_')
            ? route('admin.fake-orders.index', substr($returnTo, 5))
            : route('admin.orders.index', $returnTo);
    @endphp
    <div class="page-heading"><div><h1>Edit Order</h1><p>#{{ $order->id }} order details.</p></div><a href="{{ $backUrl }}" style="color:#d95800;text-decoration:none"><i class="fa-solid fa-arrow-left"></i> Back to List</a></div>
    <section class="panel" style="max-width:760px">
        <form method="POST" action="{{ route('admin.orders.update', $order) }}">
            @csrf @method('PUT')
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
            @foreach([['name','Name','text'],['phone','Phone Number','tel'],['email','Email','email']] as [$name,$label,$type])
                <label for="{{ $name }}" style="display:block;margin-bottom:6px;font-weight:600">{{ $label }} @if($name !== 'email')<span style="color:#dc3545">*</span>@endif</label>
                <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $order->$name) }}" @required($name !== 'email') style="width:100%;padding:11px 12px;border:1px solid #ebded4;border-radius:9px;font:inherit">
                @error($name)<div style="color:#dc3545;font-size:13px">{{ $message }}</div>@enderror<div style="height:12px"></div>
            @endforeach
            <label for="address" style="display:block;margin-bottom:6px;font-weight:600">Address *</label><textarea id="address" name="address" rows="4" required style="width:100%;padding:11px 12px;border:1px solid #ebded4;border-radius:9px;font:inherit;resize:vertical">{{ old('address', $order->address) }}</textarea>@error('address')<div style="color:#dc3545;font-size:13px">{{ $message }}</div>@enderror<div style="height:12px"></div>
            @include('dasgboard.pages.orders.product-selector')<div style="height:12px"></div>
            <div class="order-fields-grid">
                <div><label for="quantity" style="display:block;margin-bottom:6px;font-weight:600">Quantity *</label><input id="quantity" name="quantity" type="number" min="1" max="9999" value="{{ old('quantity', $order->quantity) }}" required style="width:100%;padding:11px 12px;border:1px solid #ebded4;border-radius:9px;font:inherit">@error('quantity')<div style="color:#dc3545;font-size:13px">{{ $message }}</div>@enderror</div>
                <div><label for="unit_price" style="display:block;margin-bottom:6px;font-weight:600">Unit Price *</label><input id="unit_price" name="unit_price" type="number" min="0" value="{{ old('unit_price', $order->unit_price) }}" required style="width:100%;padding:11px 12px;border:1px solid #ebded4;border-radius:9px;font:inherit">@error('unit_price')<div style="color:#dc3545;font-size:13px">{{ $message }}</div>@enderror</div>
            </div>
            <div style="height:12px"></div><label for="status" style="display:block;margin-bottom:6px;font-weight:600">Status *</label><select id="status" name="status" required style="width:100%;padding:11px 12px;border:1px solid #ebded4;border-radius:9px;font:inherit">@foreach(['pending'=>'Pending','shipping'=>'Shipping','delivered'=>'Delivered','cancelled'=>'Cancelled','refunded'=>'Refunded','fake'=>'Fake'] as $value=>$label)<option value="{{ $value }}" @selected(old('status', $order->status)===$value)>{{ $label }}</option>@endforeach</select>
            <div style="display:flex;gap:10px;margin-top:22px"><button type="submit" style="padding:11px 18px;border:0;border-radius:9px;background:#ff6b00;color:#fff;cursor:pointer;font:inherit;font-weight:600"><i class="fa-solid fa-floppy-disk"></i> Update</button><a href="{{ $backUrl }}" style="padding:10px 18px;border:1px solid #ebded4;border-radius:9px;color:#76665d;text-decoration:none">Cancel</a></div>
        </form>
    </section>
@endsection
