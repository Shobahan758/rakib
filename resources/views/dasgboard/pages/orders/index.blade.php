@extends('dasgboard.layouts.app')

@section('title', $pageTitle)

@push('styles')
<style>
.orders-table{min-width:1050px;table-layout:fixed;white-space:normal}
.orders-table th,.orders-table td{padding-inline:9px;vertical-align:middle;overflow-wrap:anywhere}
.orders-table td small{min-width:0;max-width:100%}
.orders-table th:nth-child(1){width:8%}.orders-table th:nth-child(2){width:8%}.orders-table th:nth-child(3){width:16%}.orders-table th:nth-child(4){width:10%}.orders-table th:nth-child(5){width:8%}.orders-table th:nth-child(6){width:12%}.orders-table th:nth-child(7){width:6%}.orders-table th:nth-child(8){width:7%}.orders-table th:nth-child(9){width:13%}.orders-table th:nth-child(10){width:12%}
.order-status-form{display:grid;gap:6px}.order-status-form select,.order-status-form button{width:100%;min-width:0}.order-actions{display:grid;gap:7px}.order-actions a,.order-actions button{display:flex;align-items:center;justify-content:center;width:100%;gap:5px}
@media(max-width:1200px){.orders-table{min-width:980px}}
</style>
@endpush

@section('content')
    <div class="page-heading"><div><h1>{{ $pageTitle }}</h1><p>{{ !empty($isFakeList) ? 'Review and manage orders marked as fake.' : 'Review and manage orders.' }}</p></div><div style="display:flex;align-items:center;gap:14px"><strong>Total: {{ $orders->total() }}</strong><a href="{{ route('admin.orders.create', ['return_to' => !empty($isFakeList) ? 'fake_'.$filter : $filter]) }}" style="display:inline-flex;align-items:center;gap:7px;padding:10px 14px;border-radius:9px;background:#ff6b00;color:#fff;text-decoration:none;font-weight:600"><i class="fa-solid fa-plus"></i> Create Order</a></div></div>
    @if(session('success'))<div style="margin-bottom:18px;padding:12px;border-radius:9px;background:#fff0e3;color:#d95800">{{ session('success') }}</div>@endif
    @if(!empty($isFakeList))<p>Automatic detection অথবা manual review-তে Fake হিসেবে চিহ্নিত order এখানে দেখাবে।</p>@endif
    @php
        $returnTo = !empty($isFakeList) ? 'fake_'.$filter : $filter;
    @endphp
    <section class="panel">
        <div class="table-wrap"><table class="orders-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Product</th><th>Phone</th><th>Email</th><th>Address</th><th>Quantity</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                    <tr><td>#{{ $order->id }}
                        @if($order->parent_order_id)<small style="display:block;color:#d95800">একই ডেলিভারি: <a href="{{ route('admin.orders.edit', ['order' => $order->parent_order_id, 'return_to' => $returnTo]) }}">#{{ $order->parent_order_id }}</a><br>অতিরিক্ত ডেলিভারি চার্জ নেই</small>@endif
                        @foreach($order->deliveryAddons as $addon)<small style="display:block;color:#d95800">সঙ্গে পাঠান: #{{ $addon->id }} — {{ $addon->burger_type }} × {{ $addon->quantity }}</small>@endforeach
                    </td><td><strong>{{ $order->name }}</strong></td><td>{{ $order->burger_type ?: '—' }}</td><td>{{ $order->phone }}</td><td>{{ $order->email ?: '—' }}</td><td title="{{ $order->address }}">{{ \Illuminate\Support\Str::limit($order->address, 35) }}</td><td>{{ $order->quantity }}</td><td>৳{{ number_format($order->total) }}</td><td><form class="order-status-form" method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf @method('PATCH')<select name="status" style="padding:7px;border:1px solid #ebded4;border-radius:8px"><option value="pending" @selected($order->status==='pending')>Pending</option><option value="shipping" @selected($order->status==='shipping')>Shipping</option><option value="delivered" @selected($order->status==='delivered')>Delivered</option><option value="cancelled" @selected($order->status==='cancelled')>Cancelled</option><option value="fake" @selected($order->status==='fake')>Fake</option></select><button type="submit" style="padding:7px 11px;border:0;border-radius:8px;background:#ff6b00;color:#fff;cursor:pointer;font:inherit;font-weight:600"><i class="fa-solid fa-check"></i> Update</button></form></td><td><div class="order-actions"><a href="{{ route('admin.orders.edit', ['order' => $order, 'return_to' => $returnTo]) }}" style="padding:7px 10px;border-radius:7px;background:#fff0e3;color:#d95800;text-decoration:none"><i class="fa-solid fa-pen"></i> Edit</a><form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete this order?')">@csrf @method('DELETE')<input type="hidden" name="return_to" value="{{ $returnTo }}"><button type="submit" style="padding:8px 10px;border:0;border-radius:7px;background:#fdebec;color:#c62828;cursor:pointer;font:inherit"><i class="fa-solid fa-trash"></i> Delete</button></form></div></td></tr>
                @empty
                    <tr><td colspan="10" class="empty"><i class="fa-regular fa-folder-open"></i> No orders found in this list.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="pagination-wrap">{{ $orders->links() }}</div>
    </section>
@endsection
