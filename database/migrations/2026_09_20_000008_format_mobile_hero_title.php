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

        $current = (string) ($content['title'] ?? '');
        if (str_contains($current, 'Furniture Polish Combo') && str_contains($current, 'কাঠের ফার্নিচার পলিশ')) {
            $content['title'] = '<span class="hero-title-main">Furniture Polish Combo –</span> <span class="text-accent">কাঠের ফার্নিচার পলিশ</span>';
            DB::table('landing_sections')->where('id', $hero->id)->update([
                'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Keep the mobile-friendly two-line title formatting.
    }
};
