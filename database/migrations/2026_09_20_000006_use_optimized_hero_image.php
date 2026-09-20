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

        // This exact uploaded JPEG is bundled below as an equivalent, much smaller WebP asset.
        if (($content['image'] ?? null) === 'landing/wszKHGQBDJfybPuci2D28pVvMc9zUlLgAL5qTBnV.jpg') {
            unset($content['image']);
            $content['image_alt'] = 'Furniture Polish ব্যবহারের আগে ও পরে কাঠের খাট';

            DB::table('landing_sections')->where('id', $hero->id)->update([
                'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // The original upload remains in storage; the optimized public copy stays the safe default.
    }
};
