<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'color')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('color', 50)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // This legacy column is not part of the current order form.
    }
};
