<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('landing_sections')) {
            $this->updateLandingSections();
        }

        if (Schema::hasTable('products')) {
            $this->updateProducts();
        }
    }

    private function updateLandingSections(): void
    {
        DB::table('landing_sections')
            ->select(['id', 'content'])
            ->orderBy('id')
            ->each(function (object $section): void {
                $content = json_decode($section->content, true);

                if (! is_array($content)) {
                    return;
                }

                $updated = $this->replaceLegacyProductCopy($content);

                if ($updated !== $content) {
                    DB::table('landing_sections')->where('id', $section->id)->update([
                        'content' => json_encode($updated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                }
            });
    }

    private function updateProducts(): void
    {
        $legacyNames = [
            'Classic Burger' => 'Furniture Polish Combo',
            'Cheese Burger' => 'Wood Shiner',
            'Chicken Burger' => 'Wood Cleaner',
            'Double Beef Burger' => 'Furniture Care Package',
            'ক্লাসিক বার্গার' => 'ফার্নিচার পলিশ কম্বো',
            'চিজ বার্গার' => 'উড শাইনার',
            'চিকেন বার্গার' => 'উড ক্লিনার',
            'ডাবল বিফ বার্গার' => 'ফার্নিচার কেয়ার প্যাকেজ',
        ];

        foreach ($legacyNames as $oldName => $newName) {
            DB::table('products')->where('name', $oldName)->update(['name' => $newName]);
        }

        DB::table('products')
            ->where('fallback_image', 'like', '%burger%')
            ->update(['fallback_image' => 'asset/images/furniture-polish-combo.png']);
    }

    public function down(): void
    {
        // Content cleanup is intentionally not reversible.
    }

    private function replaceLegacyProductCopy(array $content): array
    {
        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $content[$key] = $this->replaceLegacyProductCopy($value);
                continue;
            }

            if (is_string($value)) {
                $content[$key] = str_replace(
                    ['Cheeseburger', 'cheeseburger', 'Burgers', 'burgers', 'Burger', 'burger', 'বার্গার'],
                    ['Product', 'product', 'Products', 'products', 'Product', 'product', 'পণ্য'],
                    $value,
                );
            }
        }

        return $content;
    }
};
