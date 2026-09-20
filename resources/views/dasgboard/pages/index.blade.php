@extends('dasgboard.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-heading">
        <div><h1>Welcome, {{ auth()->user()->name }}</h1><p>View the latest landing page orders and sales.</p></div>
    </div>

    <section class="stat-grid" aria-label="Order summary">
        <article class="stat-card"><span class="stat-icon"><i class="fa-solid fa-bag-shopping"></i></span><div><small>Total Orders</small><strong>{{ number_format($orderCount) }}</strong></div></article>
        <article class="stat-card"><span class="stat-icon"><i class="fa-regular fa-clock"></i></span><div><small>Pending Orders</small><strong>{{ number_format($pendingCount) }}</strong></div></article>
        <article class="stat-card"><span class="stat-icon"><i class="fa-solid fa-bangladeshi-taka-sign"></i></span><div><small>Total Order Value</small><strong>৳{{ number_format($totalSales) }}</strong></div></article>
        <article class="stat-card"><span class="stat-icon"><i class="fa-solid fa-users"></i></span><div><small>Total Visitors</small><strong>{{ number_format($visitorCount) }}</strong><small>{{ number_format($todayVisitorCount) }} today</small></div></article>
    </section>

    <section id="orders" class="panel">
        <div class="panel-head"><h2>Recent Orders</h2><span>Latest {{ $orders->count() }}</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Order</th><th>Customer</th><th>Phone</th><th>Email</th><th>Address</th><th>Quantity</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td><strong>{{ $order->name }}</strong></td>
                            <td>{{ $order->phone }}</td>
                            <td>{{ $order->email ?: '—' }}</td>
                            <td title="{{ $order->address }}">{{ \Illuminate\Support\Str::limit($order->address, 30) }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>৳{{ number_format($order->total) }}</td>
                            <td><span class="status">{{ ucfirst($order->status) }}</span></td>
                            <td>{{ $order->created_at->format('d M, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="empty"><i class="fa-regular fa-folder-open"></i> No orders found yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $orders->links() }}</div>
    </section>
@endsection
