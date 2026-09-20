<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'regular_price')) {
            return;
        }

        foreach ([990 => 1350, 1250 => 1650, 1450 => 2000, 850 => 1080] as $offerPrice => $regularPrice) {
            DB::table('products')
                ->where('price', $offerPrice)
                ->whereNull('regular_price')
                ->when(Schema::hasColumn('products', 'is_modal_product'), fn ($query) => $query->where('is_modal_product', false))
                ->update(['regular_price' => $regularPrice]);
        }
    }

    public function down(): void
    {
        // Keep administrator-edited pricing intact during rollback.
    }
};
