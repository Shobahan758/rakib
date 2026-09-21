<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingSectionController;
use App\Http\Controllers\IncompleteOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ModalOrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TrackingController;
use App\Models\LandingSection;
use App\Models\SiteVisit;
use App\Models\TrackingSetting;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

Route::get('/media/{path}', function (string $path) {
    abort_unless(
        preg_match('/^[A-Za-z0-9_\/-]+\.[A-Za-z0-9]+$/', $path) === 1
        && Storage::disk('public')->exists($path),
        404,
    );

    return response()->file(Storage::disk('public')->path($path), [
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('path', '.*')->name('media.show');

Route::get('/', function () {
    SiteVisit::whereDate('visited_on', today())->firstOrCreate([
        'visitor_hash' => hash('sha256', request()->ip().'|'.request()->userAgent()),
    ], ['visited_on' => today(), 'path' => request()->path()]);
    $sections = LandingSection::all()->keyBy('slug');
    $products = Schema::hasTable('products')
        ? Product::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
        : collect();
    $modalProducts = $products->where('is_modal_product', true)->values();
    $products = $products->where('is_modal_product', false)->values();
    $tracking = TrackingSetting::activeValues();
    return view('landing.home', compact('sections', 'products', 'modalProducts', 'tracking'));
})->name('home');

Route::get('/sitemap.xml', function () {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        .'<url><loc>https://ss.smarteasyshop.com/</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>'
        .'</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::post('/orders/{order}/modal-products', [ModalOrderController::class, 'store'])
    ->middleware(['signed:relative', 'throttle:10,1'])->name('orders.modal-products.store');
Route::get('/orders/{order}/success/{addon}', [ModalOrderController::class, 'success'])
    ->middleware('signed:relative')->name('orders.modal-products.success');
Route::post('/incomplete-orders', [IncompleteOrderController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('incomplete-orders.store');
Route::post('/tracking/events', [TrackingController::class, 'collect'])
    ->middleware('throttle:120,1')
    ->name('tracking.events');

Route::redirect('/admin', '/admin/login');
Route::redirect('/login', '/admin/login');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard')->name('dashboard');
    Route::get('/admin/order/create', [AdminOrderController::class, 'create'])->middleware('permission:orders')->name('admin.orders.create');
    Route::post('/admin/order', [AdminOrderController::class, 'store'])->middleware('permission:orders')->name('admin.orders.store');
    Route::get('/admin/orders/{filter?}', [AdminOrderController::class, 'index'])->middleware('permission:orders')->name('admin.orders.index');
    Route::get('/admin/fake-orders/{filter?}', [AdminOrderController::class, 'fakeIndex'])->middleware('permission:fake_orders')->name('admin.fake-orders.index');
    Route::get('/admin/order/{order}/edit', [AdminOrderController::class, 'edit'])->middleware('permission:orders')->name('admin.orders.edit');
    Route::put('/admin/order/{order}', [AdminOrderController::class, 'update'])->middleware('permission:orders')->name('admin.orders.update');
    Route::delete('/admin/order/{order}', [AdminOrderController::class, 'destroy'])->middleware('permission:orders')->name('admin.orders.destroy');
    Route::get('/admin/incomplete-orders/{order}/edit', [AdminOrderController::class, 'editIncomplete'])->middleware('permission:incomplete_orders')->name('admin.incomplete-orders.edit');
    Route::put('/admin/incomplete-orders/{order}', [AdminOrderController::class, 'updateIncomplete'])->middleware('permission:incomplete_orders')->name('admin.incomplete-orders.update');
    Route::delete('/admin/incomplete-orders/{order}', [AdminOrderController::class, 'destroyIncomplete'])->middleware('permission:incomplete_orders')->name('admin.incomplete-orders.destroy');
    Route::get('/admin/incomplete-orders/{filter?}', [AdminOrderController::class, 'incompleteIndex'])->middleware('permission:incomplete_orders')->name('admin.incomplete-orders.index');
    Route::patch('/admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->middleware('permission:orders')->name('admin.orders.status');

    Route::middleware('permission:products,site_settings')->group(function () {
        Route::resource('/admin/modal-products', ProductController::class)->except('show')->parameters(['modal-products' => 'product'])->names('admin.modal-products');
        Route::get('/admin/products', [ProductController::class, 'index'])->name('admin.products.index');
        Route::get('/admin/products/create', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/admin/products', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    });

    Route::middleware('permission:site_settings')->group(function () {
        Route::get('/admin/site-settings', [LandingSectionController::class, 'index'])->name('admin.landing.index');
        Route::put('/admin/site-settings', [LandingSectionController::class, 'updateVisibility'])->name('admin.landing.visibility');
        Route::get('/admin/site-settings/{section}', [LandingSectionController::class, 'edit'])->name('admin.landing.edit');
        Route::put('/admin/site-settings/{section}', [LandingSectionController::class, 'update'])->name('admin.landing.update');
    });

    Route::middleware('permission:site_tracking')->group(function () {
        Route::get('/admin/site-tracking/{provider?}', [TrackingController::class, 'edit'])->name('admin.tracking.edit');
        Route::get('/admin/site-tracking/{provider}/check', [TrackingController::class, 'check'])->name('admin.tracking.check');
        Route::put('/admin/site-tracking/{provider}', [TrackingController::class, 'update'])->name('admin.tracking.update');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/admin/settings/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/settings/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/settings/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/settings/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    });

    Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('logout');
});
