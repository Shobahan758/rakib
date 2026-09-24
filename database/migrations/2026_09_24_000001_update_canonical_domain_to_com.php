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

        $section = DB::table('landing_sections')->where('slug', 'seo')->first();
        if (! $section) {
            return;
        }

        $content = json_decode($section->content, true);
        if (! is_array($content)) {
            return;
        }

        $previousDomains = [
            'https://ss.smarteasyshop.com/',
            'https://furniturepolish.solutionmart.net/',
        ];

        if (! in_array($content['canonical_url'] ?? null, $previousDomains, true)) {
            return;
        }

        $content['canonical_url'] = 'https://furniturepolish.solutionmart.com/';

        DB::table('landing_sections')->where('id', $section->id)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Production URLs are intentionally not reverted.
    }
};
