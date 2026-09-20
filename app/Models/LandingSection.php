<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSection extends Model
{
    protected $fillable = ['slug', 'content', 'is_visible'];

    protected function casts(): array
    {
        return ['content' => 'array', 'is_visible' => 'boolean'];
    }

    private static function productDefaults(): array
    {
        $defaults  = [
            'site'          => ['site_name' => 'Solution Mart', 'phone' => '01700-000000', 'phone_link' => '01700000000', 'currency_symbol' => '৳', 'mobile_order_text' => 'অর্ডার করুন — মাত্র ৳৯৯০', 'copyright' => '© 2026 Solution Mart. All rights reserved.'],
            'social'        => ['whatsapp_link' => 'https://wa.me/8801700000000?text=I%20want%20to%20order%20a%20product', 'whatsapp_label' => 'Order via WhatsApp'],
            'seo'           => [
                'meta_title' => 'Furniture Polish Combo | কাঠের ফার্নিচার পলিশ | Solution Mart',
                'meta_description' => 'পুরনো কাঠের ফার্নিচার পরিষ্কার ও চকচকে করতে Furniture Polish Combo। Polish, cleaner, putty ও প্রয়োজনীয় tools সহ complete package। সারা বাংলাদেশে Cash on Delivery।',
                'meta_keywords' => 'Furniture Polish, Furniture Polish Bangladesh, কাঠের ফার্নিচার পলিশ, Furniture Polish Combo, ফার্নিচার ক্লিনার ও পলিশ, পুরনো ফার্নিচার নতুন করার পলিশ, Wood Furniture Polish',
                'meta_author' => 'Solution Mart', 'canonical_url' => 'https://ss.smarteasyshop.com/', 'robots' => 'index, follow',
                'og_title' => 'Furniture Polish Combo | কাঠের ফার্নিচার পলিশ | Solution Mart',
                'og_description' => 'পুরনো কাঠের ফার্নিচার পরিষ্কার ও চকচকে করতে Furniture Polish Combo। Cleaner, polish, putty ও প্রয়োজনীয় tools সহ complete package।',
                'og_site_name' => 'Solution Mart',
                'schema_name' => 'Furniture Polish Combo',
                'schema_description' => 'পুরনো কাঠের ফার্নিচার পরিষ্কার, যত্ন ও উজ্জ্বল করার জন্য cleaner, Wood Furniture Polish, putty এবং প্রয়োজনীয় tools-এর complete package।',
                'schema_brand' => 'Solution Mart', 'schema_category' => 'Furniture Polish',
                'schema_sku' => 'FURNITURE-POLISH-COMBO', 'schema_offer_price' => 990,
                'schema_price_currency' => 'BDT', 'schema_availability' => 'https://schema.org/InStock',
                'schema_condition' => 'https://schema.org/NewCondition',
                'schema_rating_enabled' => '0', 'schema_rating_value' => 0, 'schema_review_count' => 0, 'schema_best_rating' => 5,
            ],
            'hero'          => [
                'top_bar_text'    => '১০০% অরিজিনাল • ০১ বছরের লিখিত গ্যারান্টি • ক্যাশ অন ডেলিভারি',
                'badge'           => '✓ প্রিমিয়াম মেটাল ক্যান প্যাকেজিং',
                'title'           => 'Furniture Polish Combo – <span class="text-accent">কাঠের ফার্নিচার পলিশ</span>',
                'description'     => '১০/২০/৩০ বছরের পুরনো, ফ্যাকাসে, রং জ্বলে যাওয়া ফার্নিচার একদম নতুন করার সহজ ও কার্যকরী সমাধান।',
                'offer_text'      => 'Solution Mart-এর Furniture Polish Combo—১৩৫০ টাকার প্যাকেজ',
                'offer_highlight' => 'এখন মাত্র ৯৯০ টাকা। সারা দেশে ক্যাশ অন ডেলিভারি।',
                'image_alt'       => 'Solution Mart Furniture Polish Combo package',
                'image_1_alt'     => 'Furniture Polish ব্যবহারের আগে ও পরে কাঠের ফার্নিচার',
                'image_2_alt'     => 'Wood Furniture Polish, cleaner, putty ও tools-এর complete combo',
                'slider_autoplay' => '0',
                'slider_interval' => 4,
            ],
            'video'         => ['kicker' => 'See It in Action', 'title' => 'ফার্নিচারের পরিবর্তন নিজেই দেখুন', 'description' => 'ফার্নিচার পলিশ ব্যবহারের পদ্ধতি ও ফলাফল ভিডিওতে দেখুন।', 'video_url' => '', 'placeholder_text' => 'অ্যাডমিন প্যানেল থেকে ভিডিও যোগ করুন', 'video_fallback_text' => 'ভিডিওটি দেখতে এই লিংক খুলুন।', 'poster_alt' => 'ফার্নিচার পলিশ ব্যবহারের ভিডিও'],
            'features'      => ['kicker' => 'আমাদের বিশেষত্ব', 'title' => 'কেন আমাদের ফার্নিচার পলিশ বেছে নেবেন?', 'description' => 'পুরোনো কাঠের ফার্নিচারের যত্নে সহজ ও কার্যকর সমাধান।', 'card_1_title' => 'কার্যকর পরিষ্কার', 'card_1_text' => 'জমে থাকা ময়লা ও দাগ পরিষ্কার করতে সহায়তা করে।', 'card_2_title' => 'সহজ ব্যবহার', 'card_2_text' => 'ঘরে বসেই সহজে ব্যবহার করা যায়।', 'card_3_title' => 'দীর্ঘস্থায়ী উজ্জ্বলতা', 'card_3_text' => 'ফার্নিচারের হারানো উজ্জ্বলতা ফিরিয়ে আনে।', 'card_4_title' => 'সম্পূর্ণ কেয়ার', 'card_4_text' => 'পরিষ্কার, মেরামত ও পলিশের প্রয়োজনীয় উপকরণ একসঙ্গে।', 'card_5_title' => 'দ্রুত ডেলিভারি', 'card_5_text' => 'সারা বাংলাদেশে দ্রুত ডেলিভারি।', 'card_6_title' => 'সাশ্রয়ী মূল্য', 'card_6_text' => 'সঠিক দামে সম্পূর্ণ ফার্নিচার কেয়ার প্যাকেজ।'],
            'story'         => ['kicker' => 'ব্যবহারের নিয়ম', 'title' => 'Furniture Polish কীভাবে ব্যবহার করবেন', 'description' => 'সঠিক ফলাফলের জন্য ধাপে ধাপে Furniture Polish Combo ব্যবহার করুন।', 'list_1' => 'ফার্নিচারের ধুলা ও আলগা ময়লা পরিষ্কার করুন', 'list_2' => 'Wood Cleaner দিয়ে জমে থাকা ময়লা তুলুন', 'list_3' => 'ফাটল বা ছোট গর্তে প্রয়োজনমতো Wood Putty দিন', 'list_4' => 'পরিষ্কার ও শুকনো কাঠে Wood Furniture Polish লাগান', 'list_5' => 'স্পঞ্জ বা নরম কাপড়ে সমানভাবে ছড়িয়ে দিন', 'image_alt' => 'কাঠের ফার্নিচারে Furniture Polish Combo ব্যবহারের নিয়ম'],
            'package_comparison' => [
                'badge' => 'যা আমাদের আলাদা করে', 'title_prefix' => 'মার্কেটে সবাই প্লাস্টিক', 'title_accent' => 'আমরা দিই প্রিমিয়াম মেটাল ক্যান',
                'description' => 'ফর্মুলা থাকে বেশি সুরক্ষিত ও কার্যকর, লুক থাকে প্রিমিয়াম।', 'feature_heading' => 'বৈশিষ্ট্য',
                'premium_heading' => 'MBarii', 'premium_subheading' => 'প্রিমিয়াম মেটাল ক্যান', 'regular_heading' => 'সাধারণ', 'regular_subheading' => 'প্লাস্টিক বোতল',
                'feature_1' => 'আলো/UV থেকে ফর্মুলা সুরক্ষা', 'feature_2' => 'প্লাস্টিক-কেমিক্যাল লিকেজের ঝুঁকি নেই',
                'feature_3' => 'প্রিমিয়াম, লাগজারি লুক', 'feature_4' => 'সহজে ভাঙে না, বেশি টেকসই',
                'yes_mark' => '✓', 'no_mark' => '×', 'image_alt' => 'MBarii প্রিমিয়াম ফার্নিচার কেয়ার প্যাকেজ',
            ],
            'complete_care' => [
                'title_prefix' => 'একটি প্যাকেজেই', 'title_accent' => 'সম্পূর্ণ কেয়ার', 'subtitle' => '৮ পিস – প্রতিটি বাছাই করা সেরা ফলাফলের জন্য',
                'item_1' => 'Wood Shiner ২০০ মিলি — প্রিমিয়াম মেটাল ক্যান, ফার্নিচারের হারানো রং ও উজ্জ্বলতা ফেরায়',
                'item_2' => 'Wood Cleaner ৫৫০ মিলি — জেদি ময়লা ও চিটচিটে দাগ তোলে', 'item_3' => 'Glass Cleaner ৫৫০ মিলি — গ্লাস, আয়না ও শোকেস ঝকঝকে করে',
                'item_4' => 'উড পুটি — ফাটল ও ছোট গর্ত ভরাট করে মসৃণ ফিনিশ দেয়', 'item_5' => 'ব্রাশ + স্পঞ্জ + রুমাল + গ্লাভস + স্প্রে হেড — নিখুঁত প্রয়োগের সম্পূর্ণ টুলসেট',
                'button' => 'অর্ডার করতে চাই', 'button_link' => '#Order', 'usage_title_prefix' => 'যেসব জায়গায়', 'usage_title_accent' => 'ব্যবহার করা যাবে',
                'usage_1' => 'কাঠের ফার্নিচার', 'usage_1_icon' => 'bi-chair', 'usage_2' => 'দরজা', 'usage_2_icon' => 'bi-door-closed',
                'usage_3' => 'খাট', 'usage_3_icon' => 'bi-lamp', 'usage_4' => 'শোকেস', 'usage_4_icon' => 'bi-inbox',
                'usage_5' => 'আলমারি', 'usage_5_icon' => 'bi-building', 'usage_6' => 'র‍্যাক / শেলফ', 'usage_6_icon' => 'bi-layers-fill',
                'guarantee_title' => '✨ ১ বছরের উজ্জ্বলতা গ্যারান্টি!',
                'guarantee_text' => '১ বছরের মধ্যে উজ্জ্বলতা নষ্ট হলে—পাবেন আরও ১ বোতল পলিশ সম্পূর্ণ ফ্রি! 🎁',
                'guarantee_note' => 'উজ্জ্বলতার নিশ্চয়তা, লিখিত গ্যারান্টিতে।',
            ],
            'menu'          => ['kicker' => 'জনপ্রিয় পণ্য', 'title' => 'আমাদের সেরা পণ্যসমূহ', 'description' => 'আপনার প্রয়োজন অনুযায়ী পণ্য বেছে নিন।', 'order_link_text' => 'অর্ডার ফর্মে যান', 'popular_tag' => 'সবচেয়ে জনপ্রিয়', 'order_button_text' => 'অর্ডার করুন', 'item_1_name' => 'ফার্নিচার পলিশ কম্বো', 'item_1_text' => 'ফার্নিচার পরিষ্কার ও উজ্জ্বল করার সম্পূর্ণ প্যাকেজ', 'item_1_price' => 990, 'item_1_alt' => 'ফার্নিচার পলিশ কম্বো প্যাক', 'item_2_name' => 'উড শাইনার', 'item_2_text' => 'কাঠের হারানো রং ও উজ্জ্বলতা ফিরিয়ে আনে', 'item_2_price' => 450, 'item_2_alt' => 'কাঠের ফার্নিচারের উড শাইনার', 'item_3_name' => 'উড ক্লিনার', 'item_3_text' => 'জেদি ময়লা ও চিটচিটে দাগ পরিষ্কার করে', 'item_3_price' => 450, 'item_3_alt' => 'কাঠের ফার্নিচারের ক্লিনার', 'item_4_name' => 'ফার্নিচার কেয়ার প্যাকেজ', 'item_4_text' => 'ফার্নিচার যত্নের প্রয়োজনীয় উপকরণ একসঙ্গে', 'item_4_price' => 990, 'item_4_alt' => 'সম্পূর্ণ ফার্নিচার কেয়ার প্যাকেজ'],
            'gallery'       => ['kicker' => 'ফলাফল দেখুন', 'title' => 'ব্যবহারের আগে ও পরের পরিবর্তন', 'image_1_alt' => 'ফার্নিচার পলিশের আগে ও পরে', 'image_2_alt' => 'ঝকঝকে কাঠের ফার্নিচার', 'image_3_alt' => 'ফার্নিচার কেয়ার প্যাকেজ', 'image_4_alt' => 'পলিশ করা কাঠের ফার্নিচার', 'image_5_alt' => 'উড ক্লিনার ও শাইনার', 'image_6_alt' => 'পরিষ্কার ও উজ্জ্বল ফার্নিচার'],
            'reviews'       => ['kicker' => 'গ্রাহকের মতামত', 'title' => 'যাচাইকৃত গ্রাহকের Furniture Polish অভিজ্ঞতা', 'rating_summary_visible' => '0', 'rating' => '', 'rating_stars' => '', 'rating_count' => '', 'verified_label' => 'Verified Customer', 'no_reviews_text' => 'যাচাইকৃত customer review যোগ হলে এখানে দেখানো হবে।', 'review_1_rating' => '', 'review_1_name' => '', 'review_1_avatar' => '', 'review_1_text' => '', 'review_2_rating' => '', 'review_2_name' => '', 'review_2_avatar' => '', 'review_2_text' => '', 'review_3_rating' => '', 'review_3_name' => '', 'review_3_avatar' => '', 'review_3_text' => ''],
            'video_reviews' => [
                'kicker'      => 'ভিডিও রিভিউ',
                'title'       => 'ভিডিওতে দেখুন ব্যবহারের অভিজ্ঞতা',
                'description' => 'ভিডিও চালু করে বিস্তারিত দেখুন। আরও ভিডিও দেখতে পাশে স্লাইড করুন।',
                'video_links' => "https://www.youtube.com/shorts/1kvRiZnBQqA\nhttps://www.youtube.com/watch?v=rBF18mklgaE",
            ],
            'deal'          => ['kicker' => 'আজকের সেরা অফার', 'title' => 'ফার্নিচারের সম্পূর্ণ যত্নে বিশেষ সাশ্রয়', 'description' => 'সীমিত সময়ের জন্য ফার্নিচার কেয়ার প্যাকেজে বিশেষ মূল্য।', 'old_price' => 1450, 'price' => 990, 'discount' => 'বিশেষ ছাড়', 'benefit_1' => 'ক্যাশ অন ডেলিভারি', 'benefit_2' => 'দ্রুত ডেলিভারি', 'benefit_3' => 'মানসম্মত পণ্য', 'button' => 'অর্ডার করুন', 'image_alt' => 'বিশেষ অফারের ফার্নিচার কেয়ার প্যাকেজ'],
            'order'         => ['kicker' => 'সহজ অর্ডার', 'title' => 'আপনার অর্ডারটি কনফার্ম করুন', 'description' => 'নিচের তথ্য দিন—আমাদের প্রতিনিধি দ্রুত ফোন করে অর্ডার নিশ্চিত করবেন।', 'offer_badge' => 'আজ ৩৫% ছাড়', 'price_prefix' => 'মাত্র', 'price' => 990, 'benefit_1' => 'কোনো অগ্রিম পেমেন্ট নেই', 'benefit_2' => 'পণ্য হাতে পেয়ে মূল্য দিন', 'image_alt' => 'অর্ডারের জন্য প্রস্তুত ফার্নিচার পলিশ প্যাকেজ', 'selected_product_label' => 'আপনার নির্বাচিত পণ্য', 'quantity_prefix' => 'পরিমাণ', 'name_label' => 'আপনার নাম *', 'name_placeholder' => 'যেমন: আরিফ হাসান', 'name_error' => 'আপনার নাম লিখুন।', 'phone_label' => 'মোবাইল নম্বর *', 'phone_placeholder' => '০১XXXXXXXXX', 'phone_error' => 'সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন।', 'address_label' => 'সম্পূর্ণ ঠিকানা *', 'address_placeholder' => 'বাড়ি, রোড, এলাকা, থানা ও জেলা', 'address_error' => 'আপনার সম্পূর্ণ ঠিকানা লিখুন।', 'delivery_area_label' => 'ডেলিভারি এলাকা *', 'inside_dhaka_label' => 'ঢাকার ভিতরে', 'outside_dhaka_label' => 'ঢাকার বাইরে', 'delivery_charge_label' => 'ডেলিভারি চার্জ', 'inside_delivery_charge' => 80, 'outside_delivery_charge' => 120, 'form_button' => 'অর্ডার কনফার্ম করুন', 'privacy' => 'আপনার তথ্য সম্পূর্ণ নিরাপদ। আমরা স্প্যাম করি না।', 'sending_message' => 'আপনার অর্ডার পাঠানো হচ্ছে...', 'success_message' => 'ধন্যবাদ! আপনার অর্ডার অনুরোধ গ্রহণ করা হয়েছে। আমরা শিগগিরই আপনাকে ফোন করব।', 'error_message' => 'অর্ডার পাঠানো যায়নি। আবার চেষ্টা করুন।'],
            'faq'           => ['kicker' => 'সাধারণ প্রশ্ন', 'title' => 'আপনার প্রশ্নের উত্তর', 'description' => 'আরও জানতে ফোন বা WhatsApp-এ যোগাযোগ করুন।', 'question_1' => 'ডেলিভারি পেতে কত সময় লাগে?', 'answer_1' => 'এলাকা অনুযায়ী সাধারণত ২–৪ কার্যদিবসের মধ্যে ডেলিভারি হয়।', 'question_2' => 'ক্যাশ অন ডেলিভারি আছে?', 'answer_2' => 'হ্যাঁ। পণ্য হাতে পাওয়ার পর মূল্য পরিশোধ করতে পারবেন।', 'question_3' => 'কোন কোন এলাকায় ডেলিভারি হয়?', 'answer_3' => 'সারা বাংলাদেশে কুরিয়ারের মাধ্যমে ডেলিভারি করা হয়।', 'question_4' => 'কী ধরনের ফার্নিচারে ব্যবহার করা যাবে?', 'answer_4' => 'কাঠের খাট, আলমারি, দরজা, শোকেস, টেবিল ও অন্যান্য কাঠের ফার্নিচারে ব্যবহার করা যায়।'],
            'cta'           => ['eyebrow' => 'Before the Offer Ends', 'title' => 'Order Today and Enjoy a Special Discount!', 'button' => 'Order Now'],
        ];
        $defaults['order'] += [
            'products_title'             => 'আপনার পণ্যসমূহ', 'products_empty' => 'কোনো পণ্য পাওয়া যায়নি।',
            'modal_title'                => '🎉 ধন্যবাদ! আপনার অর্ডারটি সফলভাবে সম্পন্ন হয়েছে',
            'modal_description'          => 'খুব শীঘ্রই আমাদের টিম আপনার সাথে যোগাযোগ করবে ইনশাআল্লাহ। আপনার পার্সেলটি ২-৪ কার্যদিবসের মধ্যে আপনার হাতে পৌঁছে যাবে। যেকোনো প্রয়োজনে সরাসরি কল করুন — আমরা সবসময় আপনার পাশে আছি ❤️',
            'modal_phone'                => '', 'modal_phone_link'             => '',
            'modal_image_alt'            => 'অর্ডার সফল হয়েছে',
            'modal_reminder'             => '📌 কল রিসিভ করতে ভুলবেন না — অচেনা নাম্বার থেকেই আমাদের টিম কল করবে।',
            'modal_products_title'       => 'আরও পণ্য পছন্দ করুন',
            'modal_products_description' => 'পছন্দের পণ্যটি আপনার অর্ডারের সঙ্গে যোগ করুন।',
            'modal_delivery_note'        => 'এই পণ্যগুলো আগের অর্ডারের সঙ্গে একই ডেলিভারিতে পাবেন। অতিরিক্ত ডেলিভারি চার্জ নেই।',
            'modal_buy_button'           => 'কিনতে চাই', 'modal_close_button'  => 'বন্ধ করুন',
            'modal_products_empty'       => 'এই মুহূর্তে আর কোনো পণ্য নেই। নতুন পণ্যের জন্য আবার ভিজিট করুন।',
        ];
        $defaults['site'] += [
            'primary_color'           => '#ff6b00', 'primary_dark_color' => '#d95800', 'button_end_color' => '#ff8b19',
            'accent_color'            => '#e63946', 'text_color'         => '#161616', 'muted_color'      => '#686868',
            'surface_color'           => '#fff8f1', 'page_color'         => '#ffffff',
            'base_font_size'          => 16, 'mobile_font_size'          => 16,
            'font_family'             => 'hind', 'button_hover_enabled'  => '1',
            'button_text_color'       => '#ffffff', 'button_radius'      => 14,
            'footer_background_color' => '', 'footer_text_color'         => '', 'logo_width'              => 160,
            'mobile_order_link'       => '#Order',
            'footer_visible'          => '1', 'mobile_order_visible'     => '1',
        ];
        $defaults['social']['whatsapp_visible']  = '1';
        $defaults['hero'] += [
            'slider_group_label' => 'Hero ছবিগুলো',
            'slider_item_label' => 'Hero ছবি',
        ];
        $defaults['video'] += [
            'section_label' => 'ভিডিও',
            'sound_button_label' => 'সাউন্ড চালু করুন',
            'sound_enabled_label' => 'সাউন্ড চালু হয়েছে',
        ];
        $defaults['features'] += [
            'card_1_icon' => 'bi-check2-circle', 'card_2_icon' => 'bi-clock-history',
            'card_3_icon' => 'bi-award', 'card_4_icon' => 'bi-shield-check',
            'card_5_icon' => 'bi-scooter', 'card_6_icon' => 'bi-wallet2',
        ];
        $defaults['package_comparison'] += ['yes_label' => 'হ্যাঁ', 'no_label' => 'না'];
        $defaults['gallery'] += ['track_label' => 'ছবি দেখতে পাশে স্ক্রল করুন'];
        $defaults['reviews'] += [
            'carousel_label' => 'Customer reviews',
            'track_label' => 'রিভিউ দেখতে পাশে স্ক্রল করুন',
            'review_image_alt' => 'ক্রেতার রিভিউ',
        ];
        foreach (['hero', 'deal', 'cta', 'menu', 'complete_care'] as $slug) {
            $defaults[$slug]['button_link'] = '#Order';
        }

        foreach (['gallery' => 3, 'reviews' => 4, 'video_reviews' => 5] as $slug => $seconds) {
            $defaults[$slug] += [
                'slider_autoplay'    => '1', 'slider_interval'            => $seconds,
                'slider_prev_label'  => 'আগের রিভিউ', 'slider_next_label' => 'পরের রিভিউ',
                'slider_pause_label' => 'বিরতি', 'slider_resume_label'    => 'চালু করুন',
            ];
        }
        $defaults['gallery']        = array_replace($defaults['gallery'], ['slider_prev_label' => 'আগের ছবি', 'slider_next_label' => 'পরের ছবি']);
        $defaults['hero']          += ['readable_background_color' => '', 'readable_background_visible' => '1'];
        $defaults['video_reviews'] += [
            'video_link_label' => 'ভিডিও আলাদা করে দেখুন ↗', 'video_label' => 'ভিডিও রিভিউ',
            'video_fallback_text' => 'ভিডিওটি দেখতে এই লিংক খুলুন।',
            'carousel_label' => 'ভিডিও রিভিউ', 'track_label' => 'ভিডিও দেখতে পাশে স্ক্রল করুন',
            'item_label' => 'ভিডিও', 'sound_button_label' => 'সাউন্ড চালু করুন',
            'sound_enabled_label' => 'সাউন্ড চালু হয়েছে',
        ];
        $defaults['order']         += [
            'modal_quantity_label' => 'পরিমাণ', 'modal_added_label'              => '✓ অর্ডারে যুক্ত হয়েছে',
            'modal_adding_label'   => 'যোগ করা হচ্ছে...', 'modal_adding_message' => 'আপনার অর্ডারের সঙ্গে পণ্যটি যুক্ত করা হচ্ছে...',
            'modal_total_label'    => 'মোট:',
            'modal_success_product_label' => 'আপনার অর্ডারে যুক্ত হয়েছে',
            'modal_price_label'             => 'মূল্য',
            'modal_home_button'             => 'হোম পেজে ফিরে যান',
            'modal_addon_success_message'   => 'পণ্যটি আপনার আগের অর্ডারের সঙ্গে যুক্ত হয়েছে। একই ডেলিভারিতে পাবেন—অতিরিক্ত ডেলিভারি চার্জ নেই।',
            'regular_price_label'            => 'রেগুলার',
            'offer_price_label'              => 'অফার',
            'quantity_decrease_label'        => 'পরিমাণ কমান',
            'quantity_increase_label'        => 'পরিমাণ বাড়ান',
            'order_required_message'         => 'আগে মূল অর্ডারটি সম্পন্ন করুন।',
            'session_expired_message'        => 'সেশন শেষ হয়েছে। পেজ রিফ্রেশ করে আবার অর্ডার দিন।',
            'rate_limit_message'             => 'অনেকবার চেষ্টা হয়েছে। এক মিনিট পরে আবার চেষ্টা করুন।',
            'server_error_message'           => 'সার্ভারে সমস্যা হওয়ায় অর্ডার নিশ্চিত করা যায়নি। আমাদের সাথে যোগাযোগ করুন।',
            'confirmation_error_message'     => 'অর্ডার নিশ্চিত করা যায়নি। অনুগ্রহ করে আমাদের সাথে যোগাযোগ করুন।',
            'addon_expired_message'          => 'পণ্য যোগ করার সময়সীমা শেষ হয়েছে। মূল অর্ডারটি আবার দেবেন না; আমাদের সাথে যোগাযোগ করুন।',
            'addon_server_error_message'     => 'সার্ভারে সমস্যা হয়েছে। মূল অর্ডারটি আবার দেবেন না; আমাদের সাথে যোগাযোগ করুন।',
            'addon_error_message'            => 'পণ্য যোগ করা যায়নি। আবার চেষ্টা করুন।',
        ];
        unset($defaults['order']['price']);
        return $defaults;
    }

    private static function productDefinitions(): array
    {
        $labels      = ['site' => 'General Settings', 'social' => 'Social Media', 'seo' => 'SEO Settings', 'hero' => 'Hero Section', 'video' => 'Video Section', 'features' => 'Features Section', 'story' => 'Furniture Care Experience', 'package_comparison' => 'Premium Can Comparison', 'complete_care' => 'Complete Care Package', 'menu' => 'Product Menu', 'gallery' => 'Image Gallery', 'reviews' => 'Customer Reviews', 'video_reviews' => 'Video Reviews', 'deal' => 'Special Offer', 'order' => 'Order Form', 'faq' => 'FAQ', 'cta' => 'Final Call to Action'];
        $images      = ['site' => ['image' => true], 'features' => ['images' => 6], 'reviews' => ['images' => 3, 'review_images' => 6], 'hero' => ['image' => true, 'images' => 2], 'video' => ['image' => true, 'video' => true], 'story' => ['image' => true], 'package_comparison' => ['image' => true], 'menu' => ['images' => 4], 'gallery' => ['images' => 6], 'deal' => ['image' => true], 'order' => ['image' => true], 'seo' => ['image' => true]];
        $definitions = [];
        foreach (self::productDefaults() as $slug => $defaults) {
            $fields       = [];
            $fieldLabels  = ['site_name' => 'Site Name', 'phone' => 'Display Phone Number', 'phone_link' => 'Call Phone Number', 'whatsapp_link' => 'WhatsApp Button Link', 'copyright' => 'Copyright Text'];
            $fieldLabels += [
                'title_prefix'               => 'শিরোনামের প্রথম অংশ',
                'title_accent'               => 'শিরোনামের রঙিন অংশ',
                'subtitle'                   => 'শিরোনামের নিচের লেখা',
                'feature_heading'            => 'বৈশিষ্ট্য কলামের শিরোনাম',
                'premium_heading'            => 'প্রিমিয়াম কলামের শিরোনাম',
                'premium_subheading'         => 'প্রিমিয়াম কলামের নিচের লেখা',
                'regular_heading'            => 'সাধারণ কলামের শিরোনাম',
                'regular_subheading'         => 'সাধারণ কলামের নিচের লেখা',
                'yes_mark'                   => 'সুবিধা আছে চিহ্ন',
                'no_mark'                    => 'সুবিধা নেই চিহ্ন',
                'usage_title_prefix'         => 'ব্যবহারের শিরোনামের প্রথম অংশ',
                'usage_title_accent'         => 'ব্যবহারের শিরোনামের রঙিন অংশ',
                'guarantee_title'            => 'গ্যারান্টির শিরোনাম',
                'guarantee_text'             => 'গ্যারান্টির বিস্তারিত',
                'guarantee_note'             => 'গ্যারান্টির শেষ কথা',
                'modal_title'                => 'পপআপের ধন্যবাদ শিরোনাম',
                'modal_description'          => 'অর্ডার সফল হওয়ার বার্তা',
                'modal_phone'                => 'পপআপে দেখানোর ফোন নম্বর (খালি রাখলে সাইটের নম্বর)',
                'modal_phone_link'           => 'কল করার নম্বর (যেমন: +8801700000000)',
                'modal_image_alt'            => 'পপআপের ছবির বর্ণনা',
                'modal_reminder'             => 'কল রিসিভ করার রিমাইন্ডার',
                'modal_products_title'       => 'আরও পণ্য অংশের শিরোনাম',
                'modal_products_description' => 'আরও পণ্য অংশের বর্ণনা',
                'modal_delivery_note'        => 'অতিরিক্ত পণ্যের ডেলিভারি বার্তা',
                'modal_buy_button'           => 'পণ্য যোগ করার বাটনের লেখা',
                'modal_close_button'         => 'পপআপ বন্ধ করার বাটনের লেখা',
                'modal_products_empty'       => 'পণ্য না থাকলে দেখানোর বার্তা',
                'modal_success_product_label' => 'Success page-এ পণ্যের উপরের লেখা',
                'modal_price_label'           => 'Success page-এর মূল্য label',
                'modal_home_button'           => 'Success page-এর হোম বাটনের লেখা',
                'modal_addon_success_message' => 'অতিরিক্ত পণ্য সফলভাবে যুক্ত হওয়ার বার্তা',
                'offer_text'                 => 'Hero ছবির নিচের অফার লেখা',
                'offer_highlight'            => 'Hero অফারের রঙিন লেখা',
                'regular_price_label'        => 'রেগুলার মূল্যের label',
                'offer_price_label'          => 'অফার মূল্যের label',
                'quantity_decrease_label'    => 'পরিমাণ কমানোর button label',
                'quantity_increase_label'    => 'পরিমাণ বাড়ানোর button label',
                'sound_button_label'         => 'ভিডিও sound button-এর লেখা',
                'sound_enabled_label'        => 'Sound চালু হওয়ার পরের লেখা',
                'carousel_label'             => 'Slider-এর screen-reader label',
                'track_label'                => 'Slider ব্যবহারের নির্দেশনা',
                'review_image_alt'           => 'Review ছবির alt text',
                'canonical_url'              => 'Canonical URL',
                'schema_name'                => 'Schema product name',
                'schema_brand'               => 'Schema brand name',
                'schema_category'            => 'Schema product category',
                'schema_sku'                 => 'Schema product SKU',
                'schema_offer_price'         => 'Schema offer price',
                'schema_price_currency'      => 'Schema currency code',
                'schema_availability'        => 'Schema availability URL',
                'schema_condition'           => 'Schema item condition URL',
                'schema_rating_enabled'      => 'Verified AggregateRating schema',
                'schema_rating_value'        => 'Verified average rating',
                'schema_review_count'        => 'Verified review count',
                'schema_best_rating'         => 'Maximum rating value',
            ];
            if ($slug === 'video_reviews') {
                $fieldLabels['video_links'] = 'ভিডিও লিংকগুলো — প্রতি লাইনে একটি';
            }

            foreach ($defaults as $key => $value) {
                $type = (is_int($value) || is_float($value)) ? 'number' : (str_contains($key, 'description') || str_contains($key, 'text') || str_contains($key, 'answer') || str_contains($key, 'message') || str_contains($key, 'reminder') ? 'textarea' : 'text');
                if ($key === 'video_links') {
                    $type = 'textarea';
                }

                if (str_ends_with($key, '_color')) {
                    $type = 'color';
                }

                if (isset(self::selectOptions()[$key])) {
                    $type = 'select';
                }

                $fields[$key] = [$fieldLabels[$key] ?? ucwords(str_replace('_', ' ', $key)), $type];
            }
            if (! in_array($slug, ['site', 'social', 'seo'], true)) {
                $layout = self::layoutFields();
                if (empty($images[$slug])) {
                    foreach (array_keys($layout) as $key) {
                        if (str_contains($key, 'image_')) {
                            unset($layout[$key]);
                        }

                    }
                }
                $fields += $layout;
            }
            $definitions[$slug] = ['label' => $labels[$slug], 'fields' => $fields] + ($images[$slug] ?? []);
        }
        return $definitions;
    }

    public static function selectOptions(): array
    {
        $visibility = ['1' => 'চালু / Show', '0' => 'বন্ধ / Hide'];
        return [
            'footer_visible'              => $visibility, 'mobile_order_visible'        => $visibility,
            'whatsapp_visible'            => $visibility, 'slider_autoplay'             => $visibility,
            'button_hover_enabled'        => $visibility, 'readable_background_visible' => $visibility,
            'rating_summary_visible'      => $visibility,
            'schema_rating_enabled'       => ['0' => 'বন্ধ — verified data নেই', '1' => 'চালু — verified rating/count আছে'],
            'schema_availability'         => [
                'https://schema.org/InStock' => 'In stock',
                'https://schema.org/OutOfStock' => 'Out of stock',
                'https://schema.org/PreOrder' => 'Pre-order',
            ],
            'schema_condition'            => [
                'https://schema.org/NewCondition' => 'New',
                'https://schema.org/UsedCondition' => 'Used',
                'https://schema.org/RefurbishedCondition' => 'Refurbished',
            ],
            'font_family'                 => ['hind' => 'Hind Siliguri (বাংলা)', 'system' => 'System font'],
            'section_text_align'          => ['' => 'Default', 'left' => 'Left', 'center' => 'Center', 'right' => 'Right'],
            'section_image_fit'           => ['' => 'Default', 'contain' => 'Contain — সম্পূর্ণ ছবি', 'cover' => 'Cover — জায়গা পূরণ'],
            'section_background_position' => ['' => 'Default', 'center' => 'Center', 'top' => 'Top', 'bottom' => 'Bottom', 'left' => 'Left', 'right' => 'Right'],
            'section_background_size'     => ['' => 'Default', 'cover' => 'Cover', 'contain' => 'Contain', 'auto' => 'Original size'],
        ];
    }

    public static function layoutFields(): array
    {
        return [
            'section_text_align'            => ['Text alignment', 'select'],
            'section_line_height'           => ['Line spacing (যেমন: 1.8)', 'number'],
            'section_button_color'          => ['Button background color', 'color'],
            'section_button_text_color'     => ['Button text color', 'color'],
            'section_button_radius'         => ['Button corner radius (px)', 'number'],
            'section_card_color'            => ['Card background color', 'color'],
            'section_card_text_color'       => ['Card text color', 'color'],
            'section_image_width'           => ['Image maximum width (px)', 'number'],
            'section_image_height'          => ['Desktop image height (px)', 'number'],
            'section_mobile_image_height'   => ['Mobile image height (px)', 'number'],
            'section_image_radius'          => ['Image corner radius (px)', 'number'],
            'section_image_fit'             => ['Image fitting', 'select'],
            'section_min_height'            => ['Desktop minimum height (px)', 'number'],
            'section_mobile_min_height'     => ['Mobile minimum height (px)', 'number'],
            'section_padding_top'           => ['Desktop top spacing (px)', 'number'],
            'section_padding_bottom'        => ['Desktop bottom spacing (px)', 'number'],
            'section_mobile_padding_top'    => ['Mobile top spacing (px)', 'number'],
            'section_mobile_padding_bottom' => ['Mobile bottom spacing (px)', 'number'],
            'section_background_color'      => ['Section background color', 'color'],
            'section_text_color'            => ['Section text color', 'color'],
            'section_heading_color'         => ['Section heading color', 'color'],
            'section_heading_size'          => ['Desktop heading size (px)', 'number'],
            'section_mobile_heading_size'   => ['Mobile heading size (px)', 'number'],
            'section_text_size'             => ['Desktop description size (px)', 'number'],
            'section_mobile_text_size'      => ['Mobile description size (px)', 'number'],
            'section_background_position'   => ['Background image position', 'select'],
            'section_background_size'       => ['Background image sizing', 'select'],
        ];
    }

    public static function reviewItems(?array $content = null): array
    {
        $content ??= self::defaults('reviews');
        if (! empty($content['reviews']) && is_array($content['reviews'])) {
            return array_values($content['reviews']);
        }

        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            if (! filled($content["review_{$i}_text"] ?? null)) {
                continue;
            }

            $items[] = [
                'image_key' => "image_{$i}",
                'rating' => $content["review_{$i}_rating"] ?? '★★★★★',
                'text' => $content["review_{$i}_text"] ?? '',
                'avatar' => $content["review_{$i}_avatar"] ?? '',
                'name' => $content["review_{$i}_name"] ?? '',
                'location' => $content["review_{$i}_location"] ?? '',
            ];
        }

        return $items;
    }

    public static function defaults(string $slug): array
    {
        return self::productDefaults()[$slug] ?? [];
    }

    public static function galleryImageKeys(array $content): array
    {
        $keys = array_keys(array_filter($content, fn($value, $key) =>
            preg_match('/^image_[1-9][0-9]*$/D', $key) && is_string($value) && $value !== '', ARRAY_FILTER_USE_BOTH));
        // Demo images are only a fallback until the gallery has uploaded photos.
        if ($keys === []) {
            $keys = array_filter(array_map(fn($i) => "image_{$i}", range(1, 6)),
                fn($key) => ! array_key_exists($key, $content));
        }
        natsort($keys);
        return array_values($keys);
    }

    public static function reviewImageKeys(array $content): array
    {
        $keys = array_keys(array_filter($content, fn($value, $key) =>
            preg_match('/^review_image_[1-9][0-9]*$/D', $key) && is_string($value) && $value !== '', ARRAY_FILTER_USE_BOTH));
        natsort($keys);
        return array_values($keys);
    }

    public static function heroImageKeys(array $content): array
    {
        $keys = array_keys(array_filter($content, fn ($value, $key) =>
            preg_match('/^image_[1-9][0-9]*$/D', $key) && is_string($value) && $value !== '', ARRAY_FILTER_USE_BOTH));
        foreach (['image_1', 'image_2'] as $fallbackKey) {
            if (! array_key_exists($fallbackKey, $content)) $keys[] = $fallbackKey;
        }
        $keys = array_values(array_unique($keys));
        natsort($keys);
        return array_values($keys);
    }

    public static function definitions(): array
    {
        return self::productDefinitions();
    }
}
