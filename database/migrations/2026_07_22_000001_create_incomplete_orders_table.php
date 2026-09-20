<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomplete_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->string('name', 100);
            $table->string('phone', 20)->index();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomplete_orders');
    }
};
