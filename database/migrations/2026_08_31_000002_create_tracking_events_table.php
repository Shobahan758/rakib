<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name', 50);
            $table->string('visitor_hash', 64);
            $table->string('path')->default('/');
            $table->date('occurred_on');
            $table->timestamps();
            $table->index(['event_name', 'occurred_on']);
            $table->index('visitor_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
