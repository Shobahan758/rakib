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
            return;
        }

        if (! Schema::hasColumn('products', 'regular_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('regular_price')->nullable()->after('price');
            });
        }

        $products = DB::table('products')
            ->when(Schema::hasColumn('products', 'is_modal_product'), fn ($query) => $query->where('is_modal_product', false))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(4)
            ->get(['id', 'regular_price']);

        foreach ($products as $index => $product) {
            $regularPrice = [1350, 1650, 2000, 1080][$index] ?? null;

            if ($regularPrice !== null && $product->regular_price === null) {
                DB::table('products')->where('id', $product->id)->update(['regular_price' => $regularPrice]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'regular_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('regular_price');
            });
        }
    }
};
