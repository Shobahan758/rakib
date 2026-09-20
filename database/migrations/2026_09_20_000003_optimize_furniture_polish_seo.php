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

        $this->mergeSection('seo', [
            'meta_title' => 'Furniture Polish Combo | কাঠের ফার্নিচার পলিশ | Solution Mart',
            'meta_description' => 'পুরনো কাঠের ফার্নিচার পরিষ্কার ও চকচকে করতে Furniture Polish Combo। Polish, cleaner, putty ও প্রয়োজনীয় tools সহ complete package। সারা বাংলাদেশে Cash on Delivery।',
            'meta_keywords' => 'Furniture Polish, Furniture Polish Bangladesh, কাঠের ফার্নিচার পলিশ, Furniture Polish Combo, ফার্নিচার ক্লিনার ও পলিশ, পুরনো ফার্নিচার নতুন করার পলিশ, Wood Furniture Polish',
            'meta_author' => 'Solution Mart',
            'canonical_url' => 'https://ss.smarteasyshop.com/',
            'robots' => 'index, follow',
            'og_title' => 'Furniture Polish Combo | কাঠের ফার্নিচার পলিশ | Solution Mart',
            'og_description' => 'পুরনো কাঠের ফার্নিচার পরিষ্কার ও চকচকে করতে Furniture Polish Combo। Cleaner, polish, putty ও প্রয়োজনীয় tools সহ complete package।',
            'og_site_name' => 'Solution Mart',
            'schema_name' => 'Furniture Polish Combo',
            'schema_description' => 'পুরনো কাঠের ফার্নিচার পরিষ্কার, যত্ন ও উজ্জ্বল করার জন্য cleaner, Wood Furniture Polish, putty এবং প্রয়োজনীয় tools-এর complete package।',
            'schema_brand' => 'Solution Mart',
            'schema_category' => 'Furniture Polish',
            'schema_sku' => 'FURNITURE-POLISH-COMBO',
            'schema_offer_price' => 990,
            'schema_price_currency' => 'BDT',
            'schema_availability' => 'https://schema.org/InStock',
            'schema_condition' => 'https://schema.org/NewCondition',
            'schema_rating_enabled' => '0',
            'schema_rating_value' => 0,
            'schema_review_count' => 0,
            'schema_best_rating' => 5,
        ]);

        $this->mergeSection('hero', [
            'title' => 'Furniture Polish Combo – <span class="text-accent">কাঠের ফার্নিচার পলিশ</span>',
            'description' => 'পুরনো ও ফ্যাকাসে কাঠের ফার্নিচার পরিষ্কার, যত্ন ও চকচকে করার complete Furniture Polish package।',
            'image_alt' => 'Solution Mart Furniture Polish Combo package',
            'image_1_alt' => 'Furniture Polish ব্যবহারের আগে ও পরে কাঠের ফার্নিচার',
            'image_2_alt' => 'Wood Furniture Polish, cleaner, putty ও tools-এর complete combo',
        ]);

        $this->mergeSection('story', [
            'kicker' => 'ব্যবহারের নিয়ম',
            'title' => 'Furniture Polish কীভাবে ব্যবহার করবেন',
            'description' => 'সঠিক ফলাফলের জন্য ধাপে ধাপে Furniture Polish Combo ব্যবহার করুন।',
            'list_1' => 'ফার্নিচারের ধুলা ও আলগা ময়লা পরিষ্কার করুন',
            'list_2' => 'Wood Cleaner দিয়ে জমে থাকা ময়লা তুলুন',
            'list_3' => 'ফাটল বা ছোট গর্তে প্রয়োজনমতো Wood Putty দিন',
            'list_4' => 'পরিষ্কার ও শুকনো কাঠে Wood Furniture Polish লাগান',
            'list_5' => 'স্পঞ্জ বা নরম কাপড়ে সমানভাবে ছড়িয়ে দিন',
            'list_6' => 'সম্পূর্ণ শুকানো পর্যন্ত ফার্নিচার ব্যবহার না করাই ভালো',
            'image_alt' => 'কাঠের ফার্নিচারে Furniture Polish Combo ব্যবহারের নিয়ম',
        ]);

        $this->mergeSection('features', [
            'title' => 'Furniture Polish Combo-এর প্রধান সুবিধা',
            'description' => 'কাঠের ফার্নিচার পরিষ্কার, যত্ন ও উজ্জ্বল রাখার জন্য প্রয়োজনীয় সুবিধা।',
        ]);

        $this->mergeSection('reviews', [
            'title' => 'যাচাইকৃত গ্রাহকের Furniture Polish অভিজ্ঞতা',
            'rating_summary_visible' => '0',
            'no_reviews_text' => 'যাচাইকৃত customer review যোগ হলে এখানে দেখানো হবে।',
        ], true);

        $this->mergeSection('package_comparison', [
            'image_alt' => 'Furniture Polish-এর premium metal can ও plastic bottle comparison',
        ]);
        $this->mergeSection('deal', [
            'image_alt' => 'Solution Mart Furniture Polish Combo offer package',
        ]);
        $this->mergeSection('order', [
            'image_alt' => 'অর্ডারের জন্য Solution Mart Furniture Polish Combo package',
        ]);

        DB::table('landing_sections')
            ->whereIn('slug', ['general', 'why', 'benefits', 'how-to-order', 'offer', 'footer'])
            ->delete();

        if (Schema::hasTable('products')) {
            DB::table('products')
                ->where('fallback_image', 'asset/images/furniture-polish-combo.png')
                ->update(['fallback_image' => 'asset/images/furniture-polish-combo.webp']);
        }
    }

    private function mergeSection(string $slug, array $updates, bool $removeDefaultReviews = false): void
    {
        $row = DB::table('landing_sections')->where('slug', $slug)->first();
        $content = $row ? json_decode($row->content, true) : [];
        $content = is_array($content) ? $content : [];

        if ($removeDefaultReviews) {
            $legacyDefaults = [
                1 => ['Tanvir Ahmed', 'পুরোনো ফার্নিচারে ব্যবহার করে খুব ভালো ফল পেয়েছি। আবার অর্ডার করব।'],
                2 => ['Sadia Islam', 'ব্যবহার করা সহজ এবং প্যাকেজিং খুব ভালো ছিল। সময়মতো ডেলিভারি পেয়েছি।'],
                3 => ['Rafsan Kabir', 'ফার্নিচারের পুরোনো উজ্জ্বলতা ফিরে এসেছে। প্যাকেজটি দামের তুলনায় ভালো।'],
            ];
            foreach ($legacyDefaults as $index => [$name, $text]) {
                if (($content["review_{$index}_name"] ?? null) === $name
                    && ($content["review_{$index}_text"] ?? null) === $text) {
                    foreach (['rating', 'name', 'avatar', 'text'] as $field) {
                        $content["review_{$index}_{$field}"] = '';
                    }
                }
            }
        }

        $payload = [
            'content' => json_encode(array_merge($content, $updates), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_visible' => $row?->is_visible ?? true,
            'updated_at' => now(),
        ];

        if ($row) {
            DB::table('landing_sections')->where('id', $row->id)->update($payload);
            return;
        }

        DB::table('landing_sections')->insert(['slug' => $slug, 'created_at' => now()] + $payload);
    }

    public function down(): void
    {
        // SEO and content cleanup is intentionally not reversible.
    }
};
