@extends('dasgboard.layouts.app')

@section('title', 'Edit Incomplete Order')

@section('content')
    <div class="page-heading">
        <div><h1>Edit Incomplete Order</h1><p>#{{ $order->id }} incomplete order details.</p></div>
        <a href="{{ route('admin.incomplete-orders.index', $filter) }}" style="color:#d95800;text-decoration:none"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
    </div>

    <section class="panel" style="max-width:760px">
        <form method="POST" action="{{ route('admin.incomplete-orders.update', $order) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="list_filter" value="{{ $filter }}">
            @php($fields = [
                ['name', 'Name', 'text', true],
                ['phone', 'Phone Number', 'tel', true],
                ['email', 'Email', 'email', false],
            ])
            @foreach($fields as [$name, $label, $type, $required])
                <label for="{{ $name }}" style="display:block;margin:0 0 6px;font-weight:600">{{ $label }} @if($required)<span style="color:#dc3545">*</span>@endif</label>
                <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $order->$name) }}" @required($required) style="width:100%;margin-bottom:4px;padding:11px 12px;border:1px solid #ebded4;border-radius:9px;font:inherit">
                @error($name)<div style="margin-bottom:10px;color:#dc3545;font-size:13px">{{ $message }}</div>@else<div style="height:10px"></div>@enderror
            @endforeach

            <label for="address" style="display:block;margin:0 0 6px;font-weight:600">Address</label>
            <textarea id="address" name="address" rows="4" style="width:100%;margin-bottom:4px;padding:11px 12px;border:1px solid #ebded4;border-radius:9px;font:inherit;resize:vertical">{{ old('address', $order->address) }}</textarea>
            @error('address')<div style="margin-bottom:10px;color:#dc3545;font-size:13px">{{ $message }}</div>@else<div style="height:10px"></div>@enderror

            <label for="quantity" style="display:block;margin:0 0 6px;font-weight:600">Quantity <span style="color:#dc3545">*</span></label>
            <input id="quantity" name="quantity" type="number" min="1" max="9999" value="{{ old('quantity', $order->quantity) }}" required style="width:100%;margin-bottom:4px;padding:11px 12px;border:1px solid #ebded4;border-radius:9px;font:inherit">
            @error('quantity')<div style="margin-bottom:10px;color:#dc3545;font-size:13px">{{ $message }}</div>@enderror

            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" style="padding:11px 18px;border:0;border-radius:9px;background:#ff6b00;color:#fff;cursor:pointer;font:inherit;font-weight:600"><i class="fa-solid fa-floppy-disk"></i> Update</button>
                <a href="{{ route('admin.incomplete-orders.index', $filter) }}" style="padding:10px 18px;border:1px solid #ebded4;border-radius:9px;color:#76665d;text-decoration:none">Cancel</a>
            </div>
        </form>
    </section>
@endsection
