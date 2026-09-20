<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->unsignedInteger('price');
                $table->string('badge', 100)->nullable();
                $table->string('image_path')->nullable();
                $table->string('fallback_image')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();
            });
        }

        if (DB::table('products')->count() === 0) {
            $now = now();
            DB::table('products')->insert([
                ['name' => 'Furniture Polish Combo', 'price' => 990, 'badge' => '🔥 সবচেয়ে জনপ্রিয়', 'fallback_image' => 'asset/images/furniture-polish-combo.webp', 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Wood Furniture Polish', 'price' => 1250, 'badge' => '🔥 জনপ্রিয় প্যাক', 'fallback_image' => 'asset/images/furniture-polish-combo.webp', 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Furniture Polish Family Combo', 'price' => 1450, 'badge' => 'সেরা মূল্য', 'fallback_image' => 'asset/images/furniture-polish-combo.webp', 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Basic Wood Furniture Polish', 'price' => 850, 'badge' => '🔥 স্টার্টার', 'fallback_image' => 'asset/images/furniture-polish-combo.webp', 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (! Schema::hasColumn('orders', 'product_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable()->after('burger_type')->index();
            });
        }
    }

    public function down(): void
    {
        // Product data is deliberately preserved during rollback.
    }
};
