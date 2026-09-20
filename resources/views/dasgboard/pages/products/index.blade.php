@extends('dasgboard.layouts.app')
@section('title', $modalMode ? 'Modal — All Product' : 'All Products (সকল পণ্য)')

@push('styles')
<style>
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.product-card {
    overflow: hidden;
    border: 1px solid var(--line);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.product-image {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #fff9f0 0%, #fff3e0 100%);
    position: relative;
    padding: 16px;
}
.product-image img {
    max-width: 170px;
    max-height: 170px;
    width: auto;
    height: auto;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.product-card:hover .product-image img {
    transform: scale(1.05);
}
.product-order-pill {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(0, 0, 0, 0.65);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
    letter-spacing: 0.5px;
}
.product-badge-pill {
    position: absolute;
    top: 12px;
    right: 12px;
    background: var(--primary, #e65100);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(230, 81, 0, 0.3);
}
.product-body {
    padding: 18px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.product-title {
    margin: 0 0 6px;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1f2937;
}
.product-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 12px 0 16px;
    padding-top: 12px;
    border-top: 1px dashed var(--line);
}
.product-price {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--primary-dark, #b33900);
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}
.status-pill.active {
    background: #e5f8ee;
    color: #18884d;
}
.status-pill.inactive {
    background: #fdf2f2;
    color: #c62828;
}
.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
}
.product-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
}
.btn-edit {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 14px;
    border-radius: 8px;
    background: var(--primary, #e65100);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s;
}
.btn-edit:hover {
    background: var(--primary-dark, #b33900);
}
.btn-delete {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 13px;
    border: 1px solid #fed7d7;
    border-radius: 8px;
    background: #fff5f5;
    color: #c53030;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-delete:hover {
    background: #fed7d7;
    color: #9b2c2c;
}
.btn-create-product {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 8px;
    background: var(--primary, #e65100);
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(230, 81, 0, 0.25);
    transition: all 0.2s;
}
.btn-create-product:hover {
    background: var(--primary-dark, #b33900);
    transform: translateY(-1px);
}
.alert-success-box {
    margin-bottom: 20px;
    padding: 14px 18px;
    border-radius: 10px;
    background: #e6f9ed;
    border: 1px solid #b7ebd0;
    color: #155724;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}
.empty-box {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 1px dashed var(--line);
    border-radius: 16px;
}
.empty-box i {
    font-size: 48px;
    margin-bottom: 12px;
    color: #d1d5db;
}
</style>
@endpush

@section('content')
<div class="page-heading">
    <div>
        <h1>{{ $modalMode ? 'Modal — All Product' : 'All Products (সকল পণ্য)' }}</h1>
        <p>{{ $modalMode ? 'অর্ডার সফল হওয়ার মডালে দেখানো পণ্য আপলোড, এডিট ও পরিচালনা করুন।' : 'অর্ডার সেকশনের পণ্যসমূহ পরিচালনা করুন।' }}</p>
    </div>
    <a class="btn-create-product" href="{{ route($routePrefix.'.create') }}">
        <i class="fa-solid fa-cloud-arrow-up"></i> {{ $modalMode ? 'Create Modal' : 'Upload Product (পণ্য আপলোড)' }}
    </a>
</div>

@if(session('success'))
<div class="alert-success-box">
    <i class="fa-solid fa-circle-check"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

<div class="product-grid">
@forelse($products as $product)
    <article class="product-card">
        <div class="product-image">
            <span class="product-order-pill">Order #{{ $product->sort_order }}</span>
            @if($product->badge)
                <span class="product-badge-pill">{{ $product->badge }}</span>
            @endif
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy">
        </div>
        <div class="product-body">
            <h3 class="product-title">{{ $product->name }}</h3>

            <div class="product-meta">
                <span class="product-price">
                    @if($product->displayRegularPrice())
                        <del style="color:var(--muted);font-size:.8em">৳{{ number_format($product->displayRegularPrice()) }}</del>
                    @endif
                    ৳{{ number_format($product->price) }}
                </span>
                <span class="status-pill {{ $product->is_active ? 'active' : 'inactive' }}">
                    <span class="status-dot"></span>
                    {{ $product->is_active ? 'সক্রিয় (Active)' : 'নিষ্ক্রিয় (Inactive)' }}
                </span>
            </div>

            <div class="product-actions">
                <a class="btn-edit" href="{{ route($routePrefix.'.edit', $product) }}">
                    <i class="fa-solid fa-pen-to-square"></i> Edit (সম্পাদনা)
                </a>
                <form method="POST" action="{{ route($routePrefix.'.destroy', $product) }}" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই পণ্যটি ডিলিট করতে চান? (Are you sure you want to delete this product?)')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete" title="Delete Product">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            </div>
        </div>
    </article>
@empty
    <div class="empty-box" style="grid-column: 1 / -1;">
        <i class="fa-solid fa-box"></i>
        <h3>কোনো পণ্য পাওয়া যায়নি (No products found)</h3>
        <p>আপনার ল্যান্ডিং পেজে প্রদর্শন করার জন্য নতুন পণ্য যোগ করুন।</p>
        <a class="btn-create-product" href="{{ route($routePrefix.'.create') }}" style="margin-top: 14px;">
            <i class="fa-solid fa-cloud-arrow-up"></i> Upload First Product (প্রথম পণ্য আপলোড করুন)
        </a>
    </div>
@endforelse
</div>
<div class="pagination-wrap">{{ $products->links() }}</div>
@endsection
