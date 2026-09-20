<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'parent_order_id')) {
            Schema::table('orders', fn (Blueprint $table) => $table->unsignedBigInteger('parent_order_id')->nullable());
        }
        if (! Schema::hasIndex('orders', ['parent_order_id', 'product_id'], 'unique')) {
            Schema::table('orders', fn (Blueprint $table) => $table->unique(['parent_order_id', 'product_id']));
        }
        $section = DB::table('landing_sections')->where('slug', 'order')->first();
        $content = json_decode($section->content ?? '{}', true) ?: [];
        if (($content['modal_products_description'] ?? '') === 'পছন্দের পণ্যটি বেছে নিয়ে নতুন অর্ডার করুন।') {
            $content['modal_products_description'] = 'পছন্দের পণ্যটি আপনার অর্ডারের সঙ্গে যোগ করুন।';
            DB::table('landing_sections')->where('slug', 'order')->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['parent_order_id', 'product_id']);
            $table->dropColumn('parent_order_id');
        });
    }
};
