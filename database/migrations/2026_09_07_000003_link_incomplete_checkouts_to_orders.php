<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'incomplete_token')) {
            Schema::table('orders', fn (Blueprint $table) => $table->uuid('incomplete_token')->nullable());
        }
        if (! Schema::hasIndex('orders', ['incomplete_token'], 'unique')) {
            Schema::table('orders', fn (Blueprint $table) => $table->unique('incomplete_token'));
        }
        Schema::table('incomplete_orders', function (Blueprint $table) {
            $table->unsignedSmallInteger('quantity')->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['incomplete_token']);
            $table->dropColumn('incomplete_token');
        });
        // Keep the wider quantity column to preserve existing values.
    }
};
