<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Schema::hasTable('products')
            ? Product::where('is_modal_product', $this->isModal())->orderBy('sort_order')->orderBy('id')->paginate(15)->withQueryString()
            : new LengthAwarePaginator([], 0, 15, 1, ['path' => request()->url()]);

        return view('dasgboard.pages.products.index', ['products' => $products, 'modalMode' => $this->isModal(), 'routePrefix' => $this->routePrefix()]);
    }

    public function create(): View
    {
        $product = new Product;
        $product->sort_order = (Product::max('sort_order') ?? 0) + 1;
        $product->is_active = true;

        return view('dasgboard.pages.products.form', ['product' => $product, 'modalMode' => $this->isModal(), 'routePrefix' => $this->routePrefix()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_modal_product'] = $this->isModal();
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        Product::create($data);

        return redirect()->route($this->routePrefix().'.index')->with('success', 'পণ্যটি সফলভাবে যুক্ত করা হয়েছে। (Product added successfully)');
    }

    public function edit(Product $product): View
    {
        abort_unless($product->is_modal_product === $this->isModal(), 404);
        return view('dasgboard.pages.products.form', ['product' => $product, 'modalMode' => $this->isModal(), 'routePrefix' => $this->routePrefix()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_modal_product === $this->isModal(), 404);
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_modal_product'] = $this->isModal();
        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        $product->update($data);

        return redirect()->route($this->routePrefix().'.index')->with('success', 'পণ্যটি সফলভাবে আপডেট করা হয়েছে। (Product updated successfully)');
    }

    public function destroy(Product $product): RedirectResponse
    {
        abort_unless($product->is_modal_product === $this->isModal(), 404);
        abort_if($product->orders()->exists(), 422, 'এই পণ্যটির অর্ডার রয়েছে, তাই এটি মুছে ফেলা যাবে না। নিষ্ক্রিয় করে রাখুন।');
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return back()->with('success', 'পণ্যটি সফলভাবে মুছে ফেলা হয়েছে। (Product deleted successfully)');
    }

    private function isModal(): bool
    {
        return request()->routeIs('admin.modal-products.*');
    }

    private function routePrefix(): string
    {
        return $this->isModal() ? 'admin.modal-products' : 'admin.products';
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'price' => ['required', 'integer', 'min:0', 'max:99999999'],
            'regular_price' => ['nullable', 'integer', 'min:0', 'max:99999999', 'gte:price'],
            'badge' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'পণ্যের নাম লিখুন।',
            'price.required' => 'পণ্যের মূল্য লিখুন।',
            'regular_price.gte' => 'রেগুলার মূল্য অফার মূল্যের সমান বা বেশি হতে হবে।',
            'sort_order.required' => 'ডিসপ্লে সিরিয়াল নম্বর দিন।',
            'image.image' => 'একটি সঠিক ছবি (JPG, PNG, WebP) আপলোড করুন।',
            'image.max' => 'ছবির সাইজ সর্বোচ্চ ১০ MB হতে পারবে।',
        ]);
    }
}
