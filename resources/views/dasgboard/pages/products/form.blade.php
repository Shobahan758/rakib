@extends('dasgboard.layouts.app')
@section('title', $product->exists ? 'Edit Product | পণ্য সম্পাদনা' : ($modalMode ? 'Create Modal' : 'Upload Product | নতুন পণ্য আপলোড'))

@push('styles')
<style>
.product-form{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,340px);gap:22px}
.field{margin-bottom:18px}
.field label{display:block;margin-bottom:7px;font-weight:600;font-size:14px}
.field input[type="text"],.field input[type="number"]{width:100%;padding:12px 14px;border:1px solid var(--line);border-radius:9px;font:inherit;outline:none;transition:border-color .2s}
.field input:focus{border-color:var(--primary)}
.field small{display:block;margin-top:5px}
.field-help{font-size:12px;color:var(--muted);margin-top:4px}
.preview-container{width:100%;min-height:180px;max-height:260px;display:flex;align-items:center;justify-content:center;border:2px dashed var(--line);border-radius:12px;background:#fff8f2;overflow:hidden;margin-top:10px}
.preview-container img{max-width:100%;max-height:240px;object-fit:contain}
.badge-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.badge-chip{padding:4px 10px;border-radius:20px;border:1px solid var(--line);background:#f8f9fa;font-size:12px;cursor:pointer;transition:all .2s}
.badge-chip:hover{background:var(--primary);color:#fff;border-color:var(--primary)}
.save{width:100%;padding:13px 20px;border:0;border-radius:9px;background:var(--primary);color:#fff;font:inherit;font-weight:700;cursor:pointer;transition:background .2s}
.save:hover{background:var(--primary-dark)}
.back{color:var(--primary-dark);text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:6px}
@media(max-width:850px){.product-form{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="page-heading">
  <div>
    <h1>{{ $product->exists ? 'Edit Product (পণ্য সম্পাদনা)' : ($modalMode ? 'Create Modal — পণ্য আপলোড' : 'Upload New Product (নতুন পণ্য আপলোড)') }}</h1>
    <p>{{ $modalMode ? 'এখানে আপলোড করা সক্রিয় পণ্য অর্ডার সফল হওয়ার মডালে দেখাবে।' : 'এখানে পণ্য যোগ বা এডিট করলে তা ল্যান্ডিং পেজের পণ্য তালিকায় আপডেট হবে।' }}</p>
  </div>
  <a class="back" href="{{ route($routePrefix.'.index') }}"><i class="fa-solid fa-arrow-left"></i> সকল পণ্য তালিকা</a>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route($routePrefix.'.update', $product) : route($routePrefix.'.store') }}">
  @csrf
  @if($product->exists)
    @method('PUT')
  @endif

  <div class="product-form">
    <section class="panel">
      <div class="field">
        <label for="name">Product Name (পণ্যের নাম) *</label>
        <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" placeholder="যেমন: ফার্নিচার পলিশ কম্বো" required>
        @error('name')<small style="color:#dc3545">{{ $message }}</small>@enderror
      </div>

      <div class="field">
        <label for="price">Price in ৳ (মূল্য টাকা) *</label>
        <input id="price" name="price" type="number" min="0" value="{{ old('price', $product->price) }}" placeholder="যেমন: 299" required>
        @error('price')<small style="color:#dc3545">{{ $message }}</small>@enderror
      </div>

      <div class="field">
        <label for="badge">Badge / Tag (ব্যাজ বা ট্যাগ)</label>
        <input id="badge" name="badge" type="text" value="{{ old('badge', $product->badge) }}" placeholder="যেমন: 🔥 সবচেয়ে জনপ্রিয়, সেরা মূল্য">
        <div class="badge-chips">
          <span class="badge-chip" onclick="document.getElementById('badge').value='🔥 সবচেয়ে জনপ্রিয়'">🔥 সবচেয়ে জনপ্রিয়</span>
          <span class="badge-chip" onclick="document.getElementById('badge').value='🔥 স্টার্টার'">🔥 স্টার্টার</span>
          <span class="badge-chip" onclick="document.getElementById('badge').value='🔥 প্রিমিয়াম'">🔥 প্রিমিয়াম</span>
          <span class="badge-chip" onclick="document.getElementById('badge').value='সেরা মূল্য'">সেরা মূল্য</span>
          <span class="badge-chip" onclick="document.getElementById('badge').value='নতুন আইটেম'">নতুন আইটেম</span>
        </div>
        @error('badge')<small style="color:#dc3545">{{ $message }}</small>@enderror
      </div>

      <div class="field">
        <label for="sort_order">Display Order (প্রদর্শন ক্রম) *</label>
        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $product->sort_order ?? 1) }}" required>
        <div class="field-help">ছোট নম্বরগুলো ল্যান্ডিং পেজে সবার আগে দেখাবে (যেমন: 1, 2, 3)।</div>
        @error('sort_order')<small style="color:#dc3545">{{ $message }}</small>@enderror
      </div>
    </section>

    <aside class="panel">
      <div class="field">
        <label for="productImage">Product Image (পণ্যের ছবি)</label>
        <input id="productImage" type="file" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="help-product-image" onchange="previewProductImage(this)">
        @include('dasgboard.pages.image-upload-help', ['slug' => 'product', 'imageKey' => 'image', 'helpId' => 'help-product-image', 'maxImageMb' => 10])
        @error('image')<small style="color:#dc3545">{{ $message }}</small>@enderror

        <div class="preview-container">
          <img id="imagePreview" src="{{ $product->exists ? $product->imageUrl() : asset('asset/images/furniture-polish-combo.png') }}" alt="{{ $product->name ?? 'Preview' }}">
        </div>
      </div>

      <div class="field" style="margin-top:20px">
        <input type="hidden" name="is_active" value="0">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
          <input style="width:18px;height:18px;accent-color:var(--primary)" type="checkbox" name="is_active" value="1" {{ old('is_active', $product->exists ? $product->is_active : true) ? 'checked' : '' }}>
          <span>{{ $modalMode ? 'পণ্যটি মডালে সক্রিয় থাকবে (Active)' : 'পণ্যটি ল্যান্ডিং পেজে সক্রিয় থাকবে (Active)' }}</span>
        </label>
      </div>

      <button class="save" type="submit">
        <i class="fa-solid fa-cloud-arrow-up"></i> {{ $product->exists ? 'Update Product (আপডেট করুন)' : 'Upload Product (পণ্য আপলোড করুন)' }}
      </button>
    </aside>
  </div>
</form>

<script>
function previewProductImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imagePreview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
@endsection
