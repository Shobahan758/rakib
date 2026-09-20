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

        $replacements = [
            'Double Beef Burger' => 'Furniture Polish Combo',
            'Double beef' => 'Furniture Polish Combo',
            'Cheese Burger' => 'Wood Furniture Polish',
            'Chicken Burger' => 'Furniture Polish Cleaner',
            'Classic Burger' => 'Furniture Polish Combo',
            'Cheeseburger' => 'Furniture Polish Combo',
            'Burgers' => 'Furniture Polish products',
            'Burger' => 'Furniture Polish',
            'বার্গার' => 'ফার্নিচার পলিশ',
            'patty' => 'পলিশ',
        ];

        DB::table('landing_sections')->orderBy('id')->each(function (object $section) use ($replacements): void {
            $content = json_decode($section->content, true);
            if (! is_array($content)) {
                return;
            }

            array_walk_recursive($content, function (&$value) use ($replacements): void {
                if (is_string($value)) {
                    $value = str_ireplace(array_keys($replacements), array_values($replacements), $value);
                }
            });

            DB::table('landing_sections')->where('id', $section->id)->update([
                'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        // Removed unrelated copy should not be restored.
    }
};
