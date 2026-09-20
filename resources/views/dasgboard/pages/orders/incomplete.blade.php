@extends('dasgboard.layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="page-heading"><div><h1>{{ $pageTitle }}</h1><p>Customers who entered their details but did not confirm an order.</p></div><strong>Total: {{ $orders->total() }}</strong></div>
    @if(session('success'))<div style="margin-bottom:18px;padding:12px;border-radius:9px;background:#fff0e3;color:#d95800">{{ session('success') }}</div>@endif
    <section class="panel">
        <div class="table-wrap"><table>
            <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Email</th><th>Address</th><th>Quantity</th><th>Latest Update</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td><td><strong>{{ $order->name }}</strong></td><td>{{ $order->phone }}</td><td>{{ $order->email ?: '—' }}</td><td title="{{ $order->address }}">{{ $order->address ? \Illuminate\Support\Str::limit($order->address, 35) : '—' }}</td><td>{{ $order->quantity }}</td><td>{{ $order->updated_at->format('d M, Y h:i A') }}</td>
                        <td><div style="display:flex;align-items:center;gap:8px">
                            <a href="{{ route('admin.incomplete-orders.edit', ['order' => $order, 'filter' => $filter]) }}" style="display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border-radius:7px;background:#fff0e3;color:#d95800;text-decoration:none"><i class="fa-solid fa-pen"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.incomplete-orders.destroy', $order) }}" onsubmit="return confirm('Delete this incomplete order?')">@csrf @method('DELETE')<input type="hidden" name="list_filter" value="{{ $filter }}"><button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:8px 10px;border:0;border-radius:7px;background:#fdebec;color:#c62828;cursor:pointer;font:inherit"><i class="fa-solid fa-trash"></i> Delete</button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty"><i class="fa-regular fa-folder-open"></i> No incomplete orders found.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="pagination-wrap">{{ $orders->links() }}</div>
    </section>
@endsection
