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

        $story = DB::table('landing_sections')->where('slug', 'story')->first();
        if (! $story) {
            return;
        }

        $content = json_decode($story->content, true);
        if (! is_array($content) || ! array_key_exists('list_6', $content)) {
            return;
        }

        unset($content['list_6']);
        DB::table('landing_sections')->where('id', $story->id)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Intentionally keep the removed card out of the landing page.
    }
};
