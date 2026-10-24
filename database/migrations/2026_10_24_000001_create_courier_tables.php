<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->text('credentials');
            $table->timestamps();
        });
        Schema::create('courier_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->string('provider');
            $table->string('status')->default('submitting');
            $table->string('consignment_id')->nullable();
            $table->string('tracking_code')->nullable();
            $table->text('message')->nullable();
            $table->unsignedInteger('cod_amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_bookings');
        Schema::dropIfExists('courier_settings');
    }
};
