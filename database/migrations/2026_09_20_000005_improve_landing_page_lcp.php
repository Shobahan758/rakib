<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('landing_sections')) {
            return;
        }

        $hero = DB::table('landing_sections')->where('slug', 'hero')->first();
        if (! $hero) {
            return;
        }

        $content = json_decode($hero->content, true);
        if (! is_array($content)) {
            return;
        }

        // Automatic hero changes keep producing new LCP candidates. Manual dots/swipes remain available.
        $content['slider_autoplay'] = '0';

        DB::table('landing_sections')->where('id', $hero->id)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Keep the performance-safe setting; admins can re-enable autoplay if needed.
    }
};
