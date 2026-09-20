<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'risk_score')) {
            Schema::table('orders', fn (Blueprint $table) => $table->unsignedSmallInteger('risk_score')->default(0));
        }
        if (! Schema::hasColumn('orders', 'risk_reasons')) {
            Schema::table('orders', fn (Blueprint $table) => $table->json('risk_reasons')->nullable());
        }
        if (! Schema::hasColumn('orders', 'fake_marked_at')) {
            Schema::table('orders', fn (Blueprint $table) => $table->timestamp('fake_marked_at')->nullable());
        }
        if (! Schema::hasIndex('orders', ['ip_address', 'created_at'])) {
            Schema::table('orders', fn (Blueprint $table) => $table->index(['ip_address', 'created_at']));
        }
        DB::table('orders')->where('status', 'fake')->whereNull('fake_marked_at')->update(['fake_marked_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['ip_address', 'created_at']);
            $table->dropColumn(['risk_score', 'risk_reasons', 'fake_marked_at']);
        });
    }
};
