<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $setting = DB::table('tracking_settings')->where('key', 'meta_pixel_id')->first();
        if ($setting && filled($setting->value)) return;

        DB::table('tracking_settings')->updateOrInsert(
            ['key' => 'meta_pixel_id'],
            ['value' => '000000000000000', 'created_at' => $setting->created_at ?? now(), 'updated_at' => now()],
        );
    }

    public function down(): void
    {
        DB::table('tracking_settings')->where('key', 'meta_pixel_id')->where('value', '000000000000000')
            ->update(['value' => null, 'updated_at' => now()]);
    }
};
