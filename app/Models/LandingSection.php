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
            'seo'           => ['meta_title' => 'ফার্নিচার পলিশ কম্বো প্যাক – ৬ মাস ওয়ারেন্টি | Solution Mart । Furniture Polish', 'meta_description' => 'পুরনো, ফ্যাকাশে ফার্নিচার নতুনের মতো চকচকে করুন। ৬ মাস ওয়ারেন্টি, ৭ দিন মানি-ব্যাক, সারা বাংলাদেশে ফ্রি ডেলিভারি। মাত্র ৳৯৯০ থেকে।', 'meta_keywords' => 'ফার্নিচার পলিশ, ফার্নিচার পলিশ কম্বো প্যাক, কাঠের ফার্নিচার পলিশ, ফার্নিচার শাইনিং কম্বো, বার্নিশ কম্বো প্যাক, ফার্নিচার কেয়ার প্রোডাক্ট, পুরনো ফার্নিচার নতুন করার উপায়, Furniture Polish, Burnish, Ready Burnish, Ready Furniture Polish, Solution Mart', 'meta_author' => 'Solution Mart', 'og_title' => 'ফার্নিচার পলিশ কম্বো প্যাক – ৬ মাস ওয়ারেন্টি | Solution Mart', 'og_description' => 'পুরনো, ফ্যাকাশে ফার্নিচার নতুনের মতো চকচকে করুন। ৬ মাস ওয়ারেন্টি, ৭ দিন মানি-ব্যাক, সারা বাংলাদেশে ফ্রি ডেলিভারি। মাত্র ৳৯৯০ থেকে।', 'og_site_name' => 'Solution Mart', 'schema_name' => 'ফার্নিচার পলিশ কম্বো প্যাক', 'schema_description' => 'পুরনো, ফ্যাকাশে ফার্নিচার নতুনের মতো চকচকে করুন। ৬ মাস ওয়ারেন্টি, ৭ দিন মানি-ব্যাক, সারা বাংলাদেশে ফ্রি ডেলিভারি। মাত্র ৳৯৯০ থেকে।', 'schema_phone' => '+8801773974692', 'schema_cuisine' => 'Furniture Polish, Furniture Care', 'schema_price_range' => '৳৳', 'schema_area_served' => 'Bangladesh', 'schema_offer_price' => 990],
            'hero'          => [
                'top_bar_text'    => '১০০% অরিজিনাল • ০১ বছরের লিখিত গ্যারান্টি • ক্যাশ অন ডেলিভারি',
                'badge'           => '✓ প্রিমিয়াম মেটাল ক্যান প্যাকেজিং',
                'title'           => 'পুরনো ফার্নিচার <span class="text-accent">নতুনের মতো ঝকঝকে</span>',
                'description'     => '১০/২০/৩০ বছরের পুরনো, ফ্যাকাসে, রং জ্বলে যাওয়া ফার্নিচার একদম নতুন করার সহজ ও কার্যকরী সমাধান।',
                'offer_text'      => 'Solution Mart-এর Furniture Polish Combo—১৩৫০ টাকার প্যাকেজ',
                'offer_highlight' => 'এখন মাত্র ৯৯০ টাকা। সারা দেশে ক্যাশ অন ডেলিভারি।',
                'image_alt'       => 'গ্রাহকের বিশ্বাসই আমাদের আসল শক্তি - MBarii Furniture & Home Care',
                'image_1_alt'     => 'পুরনো ও পলিশ করা ফার্নিচারের তুলনা',
                'image_2_alt'     => 'MBarii ফার্নিচার কেয়ার প্যাকেজ',
                'slider_autoplay' => '1',
                'slider_interval' => 4,
            ],
            'video'         => ['kicker' => 'See It in Action', 'title' => 'ফার্নিচারের পরিবর্তন নিজেই দেখুন', 'description' => 'ফার্নিচার পলিশ ব্যবহারের পদ্ধতি ও ফলাফল ভিডিওতে দেখুন।', 'video_url' => '', 'placeholder_text' => 'অ্যাডমিন প্যানেল থেকে ভিডিও যোগ করুন', 'video_fallback_text' => 'ভিডিওটি দেখতে এই লিংক খুলুন।', 'poster_alt' => 'ফার্নিচার পলিশ ব্যবহারের ভিডিও'],
            'features'      => ['kicker' => 'আমাদের বিশেষত্ব', 'title' => 'কেন আমাদের ফার্নিচার পলিশ বেছে নেবেন?', 'description' => 'পুরোনো কাঠের ফার্নিচারের যত্নে সহজ ও কার্যকর সমাধান।', 'card_1_title' => 'কার্যকর পরিষ্কার', 'card_1_text' => 'জমে থাকা ময়লা ও দাগ পরিষ্কার করতে সহায়তা করে।', 'card_2_title' => 'সহজ ব্যবহার', 'card_2_text' => 'ঘরে বসেই সহজে ব্যবহার করা যায়।', 'card_3_title' => 'দীর্ঘস্থায়ী উজ্জ্বলতা', 'card_3_text' => 'ফার্নিচারের হারানো উজ্জ্বলতা ফিরিয়ে আনে।', 'card_4_title' => 'সম্পূর্ণ কেয়ার', 'card_4_text' => 'পরিষ্কার, মেরামত ও পলিশের প্রয়োজনীয় উপকরণ একসঙ্গে।', 'card_5_title' => 'দ্রুত ডেলিভারি', 'card_5_text' => 'সারা বাংলাদেশে দ্রুত ডেলিভারি।', 'card_6_title' => 'সাশ্রয়ী মূল্য', 'card_6_text' => 'সঠিক দামে সম্পূর্ণ ফার্নিচার কেয়ার প্যাকেজ।'],
            'story'         => ['kicker' => 'ফার্নিচারের নতুন জীবন', 'title' => 'পুরোনো ফার্নিচার নতুনের মতো ঝকঝকে করুন', 'description' => 'ক্লিনার, শাইনার ও প্রয়োজনীয় টুলসের সমন্বয়ে ঘরেই ফার্নিচারের সম্পূর্ণ যত্ন নিন।', 'list_1' => 'উড শাইনার', 'list_2' => 'উড ক্লিনার', 'list_3' => 'গ্লাস ক্লিনার', 'list_4' => 'উড পুটি', 'list_5' => 'প্রয়োজনীয় ব্রাশ ও স্পঞ্জ', 'list_6' => 'সহজ ব্যবহার', 'image_alt' => 'ফার্নিচার পলিশ ব্যবহারের আগে ও পরে'],
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
            ],
            'menu'          => ['kicker' => 'জনপ্রিয় পণ্য', 'title' => 'আমাদের সেরা পণ্যসমূহ', 'description' => 'আপনার প্রয়োজন অনুযায়ী পণ্য বেছে নিন।', 'order_link_text' => 'অর্ডার ফর্মে যান', 'popular_tag' => 'সবচেয়ে জনপ্রিয়', 'order_button_text' => 'অর্ডার করুন', 'item_1_name' => 'ফার্নিচার পলিশ কম্বো', 'item_1_text' => 'ফার্নিচার পরিষ্কার ও উজ্জ্বল করার সম্পূর্ণ প্যাকেজ', 'item_1_price' => 990, 'item_1_alt' => 'ফার্নিচার পলিশ কম্বো প্যাক', 'item_2_name' => 'উড শাইনার', 'item_2_text' => 'কাঠের হারানো রং ও উজ্জ্বলতা ফিরিয়ে আনে', 'item_2_price' => 450, 'item_2_alt' => 'কাঠের ফার্নিচারের উড শাইনার', 'item_3_name' => 'উড ক্লিনার', 'item_3_text' => 'জেদি ময়লা ও চিটচিটে দাগ পরিষ্কার করে', 'item_3_price' => 450, 'item_3_alt' => 'কাঠের ফার্নিচারের ক্লিনার', 'item_4_name' => 'ফার্নিচার কেয়ার প্যাকেজ', 'item_4_text' => 'ফার্নিচার যত্নের প্রয়োজনীয় উপকরণ একসঙ্গে', 'item_4_price' => 990, 'item_4_alt' => 'সম্পূর্ণ ফার্নিচার কেয়ার প্যাকেজ'],
            'gallery'       => ['kicker' => 'ফলাফল দেখুন', 'title' => 'ব্যবহারের আগে ও পরের পরিবর্তন', 'image_1_alt' => 'ফার্নিচার পলিশের আগে ও পরে', 'image_2_alt' => 'ঝকঝকে কাঠের ফার্নিচার', 'image_3_alt' => 'ফার্নিচার কেয়ার প্যাকেজ', 'image_4_alt' => 'পলিশ করা কাঠের ফার্নিচার', 'image_5_alt' => 'উড ক্লিনার ও শাইনার', 'image_6_alt' => 'পরিষ্কার ও উজ্জ্বল ফার্নিচার'],
            'reviews'       => ['kicker' => 'গ্রাহকের মতামত', 'title' => 'হাজারো গ্রাহকের বিশ্বস্ত পছন্দ', 'rating' => '4.9', 'rating_stars' => '★★★★★', 'rating_count' => '500+ verified reviews', 'verified_label' => 'Verified Customer', 'review_1_rating' => '★★★★★', 'review_1_name' => 'Tanvir Ahmed', 'review_1_avatar' => 'T', 'review_1_text' => 'পুরোনো ফার্নিচারে ব্যবহার করে খুব ভালো ফল পেয়েছি। আবার অর্ডার করব।', 'review_2_rating' => '★★★★★', 'review_2_name' => 'Sadia Islam', 'review_2_avatar' => 'S', 'review_2_text' => 'ব্যবহার করা সহজ এবং প্যাকেজিং খুব ভালো ছিল। সময়মতো ডেলিভারি পেয়েছি।', 'review_3_rating' => '★★★★★', 'review_3_name' => 'Rafsan Kabir', 'review_3_avatar' => 'R', 'review_3_text' => 'ফার্নিচারের পুরোনো উজ্জ্বলতা ফিরে এসেছে। প্যাকেজটি দামের তুলনায় ভালো।'],
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
        $defaults['video_reviews'] += ['video_link_label' => 'ভিডিও আলাদা করে দেখুন ↗', 'video_label' => 'ভিডিও রিভিউ', 'video_fallback_text' => 'ভিডিওটি দেখতে এই লিংক খুলুন।'];
        $defaults['order']         += [
            'modal_quantity_label' => 'পরিমাণ', 'modal_added_label'              => '✓ অর্ডারে যুক্ত হয়েছে',
            'modal_adding_label'   => 'যোগ করা হচ্ছে...', 'modal_adding_message' => 'আপনার অর্ডারের সঙ্গে পণ্যটি যুক্ত করা হচ্ছে...',
            'modal_total_label'    => 'মোট:',
            'modal_success_product_label' => 'আপনার অর্ডারে যুক্ত হয়েছে',
            'modal_price_label'             => 'মূল্য',
            'modal_home_button'             => 'হোম পেজে ফিরে যান',
            'modal_addon_success_message'   => 'পণ্যটি আপনার আগের অর্ডারের সঙ্গে যুক্ত হয়েছে। একই ডেলিভারিতে পাবেন—অতিরিক্ত ডেলিভারি চার্জ নেই।',
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
        if (array_key_exists($slug, self::productDefaults())) {
            return self::productDefaults()[$slug];
        }

        if ($slug === 'seo') {
            return [
                'meta_title'          => 'গ্যাভিট্রাল | প্রিমিয়াম হারবাল সাপ্লিমেন্ট',
                'meta_description'    => 'গ্যাভিট্রাল—দৈনন্দিন সুস্থতা বজায় রাখতে সহায়ক মানসম্মত হারবাল সাপ্লিমেন্ট। সারা বাংলাদেশে ক্যাশ অন Delivered।',
                'meta_keywords'       => 'Gavitral, গ্যাভিট্রাল, হারবাল সাপ্লিমেন্ট, বাংলাদেশ, ক্যাশ অন Delivered',
                'canonical_url'       => '',
                'robots'              => 'index, follow',
                'og_title'            => 'গ্যাভিট্রাল — আপনার দৈনন্দিন সুস্থতার বিশ্বস্ত সঙ্গী',
                'og_description'      => 'আজকের বিশেষ মূল্যে গ্যাভিট্রাল। সারা বাংলাদেশে ক্যাশ অন Delivered।',
                'schema_product_name' => 'Gavitral',
                'schema_description'  => 'দৈনন্দিন সুস্থতা বজায় রাখতে সহায়ক এক হারবাল সাপ্লিমেন্ট।',
            ];
        }

        if ($slug === 'why') {
            return [
                'eyebrow'          => 'কেন গ্যাভিট্রাল', 'title'                        => 'আপনার পছন্দে থাকুক মান ও নিশ্চিন্ততা', 'description' => 'প্রতিদিনের ব্যবহারের কথা মাথায় রেখে সহজ, পরিচ্ছন্ন ও নির্ভরযোগ্য অভিজ্ঞতা।',
                'stat_one_value'   => '৬৪', 'stat_one_label'                            => 'জেলায় Delivered', 'stat_two_value'                  => '২৪', 'stat_two_label' => 'ঘণ্টা সাপোর্ট',
                'stat_three_value' => '১০০%', 'stat_three_label'                        => 'সুরক্ষিত প্যাকেজিং', 'stat_four_value'               => '৬', 'stat_four_label' => 'বিশ্বাসযোগ্য সুবিধা',
                'card_one_icon'    => 'fa-solid fa-gem', 'card_one_title'               => 'উন্নত মানের উপাদান', 'card_one_description'          => 'মান বজায় রেখে প্রস্তুত করা হয়েছে, যাতে দৈনন্দিন রুনে সহজে যুক্ত করা যায়।',
                'card_two_icon'    => 'fa-solid fa-hand-sparkles', 'card_two_title'     => 'সহজে ব্যবহারযোগ্য', 'card_two_description'           => 'ব্যস্ত জীবনযাত্রায়ও নির্দেশনা অনুযায়ী ব্যবহার করা সহজ ও সুবিধাজনক।',
                'card_three_icon'  => 'fa-solid fa-industry', 'card_three_title'        => 'মানসম্মত উৎপাদন', 'card_three_description'           => 'পরিচ্ছন্নতা ও পণ্যের মানকে গুরুত্ব দিয়ে উৎপাদন ও প্যাকেজিং করা হয়।',
                'card_four_icon'   => 'fa-solid fa-map-location-dot', 'card_four_title' => 'দেশজুড়ে Delivered', 'card_four_description'         => 'বাংলাদেশের যেকোনো প্রান্তে নিরাপদে আপনার Addressয় পৌঁছে দেওয়া হয়।',
            ];
        }

        if ($slug === 'benefits') {
            return [
                'eyebrow'         => 'দৈনন্দিন উপকারিতা', 'title'                    => 'সুস্থ জীবনযাপনের রুনে সহজ সংযোজন', 'description'   => 'গ্যাভিট্রাল কোনো রোগ নিরাময়ের ওষুধ নয়। এ স্বাস্থ্যকর খাবার, পর্যাপ্ত ঘুম ও সক্রিয় জীবনযাপনের বিকল্পও নয়।',
                'note_title'      => 'গুরুত্বপূর্ণ:', 'note_text'                    => 'ফলাফল ব্যক্তিভেদে ভিন্ন হতে পারে। পণ্যের লেবেল ও নির্দেশনা মেনে ব্যবহার করুন। কোনো স্বাস্থ্যগত উদ্বেগ, গর্ভাবস্থা, ওষুধ সেবন বা বিশেষ শারীরিক অবস্থা থাকলে স্বাস্থ্যসেবা পেশাজীবীর পরামর্শ নিন।',
                'card_one_icon'   => 'fa-solid fa-heart-pulse', 'card_one_title'     => 'দৈনন্দিন সুস্থতা', 'card_one_description'          => 'শরীরের স্বাভাবিক সুস্থতা বজায় রাখতে সহায়ক।',
                'card_two_icon'   => 'fa-solid fa-person-walking', 'card_two_title'  => 'স্বাভাবিক কার্যক্রম', 'card_two_description'       => 'শরীরের স্বাভাবিক কার্যক্রমে সহায়তা করার জন্য তৈরি।',
                'card_three_icon' => 'fa-solid fa-briefcase', 'card_three_title'     => 'সহজে বহনযোগ্য', 'card_three_description'           => 'বাসা, অফিস কিংবা ভ্রমণে সঙ্গে রাখা সুবিধাজনক।',
                'card_four_icon'  => 'fa-solid fa-calendar-check', 'card_four_title' => 'নিয়মিত ব্যবহারের উপযোগী', 'card_four_description' => 'নির্দেশনা অনুযায়ী দৈনন্দিন রুনে সহজে মানিয়ে যায়।',
            ];
        }

        if ($slug === 'features') {
            return [
                'eyebrow'         => 'পণ্যের বৈশিষ্ট্য', 'title'                    => 'প্রতি ধাপে প্রিমিয়াম অভিজ্ঞতা', 'description' => '',
                'card_one_icon'   => 'fa-solid fa-award', 'card_one_title'          => 'প্রিমিয়াম মান', 'card_one_description'        => 'মানকে অগ্রাধিকার',
                'card_two_icon'   => 'fa-solid fa-box-open', 'card_two_title'       => 'নিরাপদ প্যাকেজিং', 'card_two_description'      => 'যত্নে মোড়ানো',
                'card_three_icon' => 'fa-solid fa-thumbs-up', 'card_three_title'    => 'সহজ ব্যবহার', 'card_three_description'         => 'রুন-বান্ধব',
                'card_four_icon'  => 'fa-solid fa-shield-halved', 'card_four_title' => 'বিশ্বস্ত পণ্য', 'card_four_description'        => 'স্বচ্ছ অভিজ্ঞতা',
                'card_five_icon'  => 'fa-solid fa-truck-fast', 'card_five_title'    => 'দ্রুত Delivered', 'card_five_description'      => 'সারা বাংলাদেশে',
                'card_six_icon'   => 'fa-solid fa-wallet', 'card_six_title'         => 'ক্যাশ অন Delivered', 'card_six_description'    => 'পেয়ে মূল্য দিন',
            ];
        }

        if ($slug === 'gallery') {
            return ['eyebrow' => 'পণ্য গ্যালারি', 'title' => 'প্রিমিয়াম প্যাকেজিং, পরিচ্ছন্ন উপস্থাপন', 'description' => 'ছবিতে ক্লিক করে বড় করে দেখুন।'];
        }

        if ($slug === 'how-to-order') {
            return [
                'eyebrow'           => 'Orderের নিয়ম', 'title' => 'মাত্র ৩ সহজ ধাপে', 'description'                  => '',
                'step_one_number'   => '১', 'step_one_icon'     => 'fa-regular fa-pen-to-square', 'step_one_title'    => 'ফর্ম পূরণ করুন', 'step_one_description'    => 'Name, Phone নম্বর ও সম্পূর্ণ Address লিখুন।',
                'step_two_number'   => '২', 'step_two_icon'     => 'fa-solid fa-phone-volume', 'step_two_title'       => 'নিশ্চিতকরণ কল', 'step_two_description'     => 'আমাদের প্রতিনিধি Phone করে Order নিশ্চিত করবেন।',
                'step_three_number' => '৩', 'step_three_icon'   => 'fa-solid fa-box-circle-check', 'step_three_title' => 'পণ্য গ্রহণ করুন', 'step_three_description' => 'ক্যাশ অন Deliveredতে পণ্য বুঝে নিয়ে মূল্য দিন।',
            ];
        }

        if ($slug === 'reviews') {
            $reviews = [
                ['র', 'রাফিয়া আক্তার', 'ঢাকা', 'প্যাকেজিং খুব ভালো ছিল। পণ্য অক্ষত অবস্থায় হাতে পেয়েছি।'], ['স', 'সাইফুল ইসলাম', 'চট্টগ্রাম', 'সময়মতো Delivered পেয়েছি। Order নিশ্চিত করার প্রক্রিয়াটাও সহজ ছিল।'],
                ['ন', 'নুসরাত জাহান', 'সিলেট', 'বোতলের আকার সুবিধাজনক। সঙ্গে রাখা ও নির্দেশনা অনুযায়ী ব্যবহার করা সহজ।'],
            ];
            $defaults = ['eyebrow' => 'ক্রেতাদের অভিজ্ঞতা', 'title' => 'যে কথাগুলো আমাদের অনুপ্রাণিত করে', 'description' => 'এখানে শুধুমাত্র Delivered, প্যাকেজিং ও ব্যবহার-অভিজ্ঞতা তুলে ধরা হয়েছে।'];
            foreach ($reviews as $index => [$avatar, $name, $location, $text]) {
                $number    = $index + 1;
                $defaults += ["review_{$number}_rating" => '★★★★★', "review_{$number}_text" => $text, "review_{$number}_avatar" => $avatar, "review_{$number}_name" => $name, "review_{$number}_location" => $location];
            }
            return $defaults;
        }

        if ($slug === 'offer') {
            return ['eyebrow' => 'সীমিত সময়ের অফার', 'title' => 'আজকের বিশেষ মূল্য', 'description' => 'অফার শেষ হওয়ার আগেই Order নিশ্চিত করুন।', 'old_price_label' => 'পুরাতন মূল্য:', 'old_price' => 1200, 'price' => 890, 'price_badge' => 'অফার মূল্য', 'countdown_title' => 'অফার শেষ হতে বাকি', 'hours' => 7, 'minutes' => 49, 'seconds' => 32, 'button_text' => 'অফার নিন'];
        }

        if ($slug === 'faq') {
            $questions = [
                ['কিভাবে Order করবো?', 'নিচের Order ফর্মে আপনার Name, সক্রিয় Phone নম্বর ও সম্পূর্ণ Address দিয়ে “Order নিশ্চিত করুন” বাটনে চাপ দিন। আমাদের প্রতিনিধি Phone করে Order নিশ্চিত করবেন।'],
                ['কত দিনে Delivered পাবো?', 'ঢাকার ভেতরে সাধারণত ১–২ কর্মদিবস এবং ঢাকার বাইরে ২–৪ কর্মদিবস সময় লাগতে পারে। এলাকা ও পরিবহন পরিস্থিতি অনুযায়ী সময় কিছুটা পরিবর্তিত হতে পারে।'],
                ['ক্যাশ অন Delivered আছে?', 'হ্যাঁ, পণ্য হাতে পাওয়ার পর Delivered কর্মীকে মূল্য পরিশোধ করতে পারবেন।'], ['সারা বাংলাদেশে Delivered হয়?', 'হ্যাঁ, কুরিয়ার সেবার আওতাভুক্ত বাংলাদেশের সব জেলা ও অধিকাংশ উপজেলায় আমরা Delivered করি।'],
                ['Delivered চার্জ কত?', 'অবস্থান ও চলমান অফারের ভিত্তিতে Delivered চার্জ নির্ধারিত হয়। Order নিশ্চিত করার Phoneে প্রতিনিধি সঠিক চার্জ জানাবেন।'],
            ];
            $defaults = ['eyebrow' => 'সাধারণ জিজ্ঞাসা', 'title' => 'আপনার প্রশ্নের সহজ উত্তর', 'description' => 'Order, Delivered ও ব্যবহার সম্পর্কে সবচেয়ে বেশি জানতে চাওয়া প্রশ্নগুলোর উত্তর এক জায়গায়।', 'metric_one_value' => '৫', 'metric_one_label' => 'প্রশ্নের উত্তর', 'metric_two_value' => '২৪/৭', 'metric_two_label' => 'সহায়তার সুযোগ', 'whatsapp_title' => 'উত্তর খুঁজে পাননি?', 'whatsapp_text' => 'হোয়াটসঅ্যাপে জিজ্ঞাসা করুন', 'whatsapp_number' => '8801700000000'];
            foreach ($questions as $index => [$question, $answer]) {
                $defaults += ['question_' . ($index + 1) => $question, 'answer_' . ($index + 1) => $answer];
            }

            return $defaults;
        }

        if ($slug === 'order') {
            return [
                'eyebrow'       => 'নিরাপদ Order', 'title'                   => 'আজই Order করুন', 'description'                                     => 'ফর্ম পূরণ করুন। Order নিশ্চিত করতে আমাদের প্রতিনিধি আপনাকে Phone করবেন।',
                'product_name'  => 'গ্যাভিট্রাল হারবাল সাপ্লিমেন্ট', 'price' => 890, 'old_price'                                                    => 1200, 'benefit_one'              => 'পণ্য হাতে পেয়ে মূল্য পরিশোধ', 'benefit_two' => 'নিরাপদ ও যত্নশীল প্যাকেজিং', 'benefit_three' => 'Phoneে Order নিশ্চিতকরণ',
                'form_title'    => 'আপনার তথ্য দিন', 'form_description'      => 'সঠিক তথ্য দিলে দ্রুত Delivered নিশ্চিত করা সহজ হবে।', 'name_label' => 'আপনার Name', 'name_placeholder' => 'যেমন: মোহাম্মদ রাকিব', 'phone_label'         => 'Phone নম্বর', 'phone_placeholder'            => '০১XXXXXXXXX', 'email_label' => 'Email (ঐচ্ছিক)', 'email_placeholder'                        => 'যেমন: name@example.com',
                'address_label' => 'সম্পূর্ণ Address', 'address_placeholder' => 'বাড়ি/রোড, এলাকা, থানা ও জেলা', 'quantity_label'                   => 'Quantity', 'minimum_quantity'   => 1, 'maximum_quantity'                         => 9999, 'total_label'                           => 'সর্বTotal', 'total_note'    => 'Delivered চার্জ প্রযোজ্য হলে পরে জানানো হবে', 'button_text' => 'Order নিশ্চিত করুন', 'privacy_text' => 'আপনার তথ্য শুধু Order প্রক্রিয়ার জন্য ব্যবহার করা হবে।',
            ];
        }

        if ($slug === 'footer') {
            return [
                'badge_one_icon'   => 'fa-solid fa-box', 'badge_one_title'                    => '১০০% নিরাপদ', 'badge_one_text' => 'প্যাকেজিং',
                'badge_two_icon'   => 'fa-solid fa-hand-holding-dollar', 'badge_two_title'    => 'ক্যাশ অন', 'badge_two_text'    => 'Delivered',
                'badge_three_icon' => 'fa-solid fa-truck-fast', 'badge_three_title'           => 'দ্রুত', 'badge_three_text'     => 'Delivered',
                'badge_four_icon'  => 'fa-solid fa-headset', 'badge_four_title'               => 'সহায়ক', 'badge_four_text'     => 'Customer সাপোর্ট',
                'copyright'        => '© {year} Gavitral. সর্বস্বত্ব সংরক্ষিত।', 'disclaimer' => 'গ্যাভিট্রাল কোনো রোগ নির্ণয়, চিকিৎসা বা নিরাময়ের উদ্দেশ্যে উপস্থাপিত নয়।',
            ];
        }

        return $slug === 'hero' ? [
            'eyebrow'        => 'সুস্থতার পথে প্রতিদিন', 'title'      => 'গ্যাভিট্রাল — আপনার দৈনন্দিন সুস্থতার বিশ্বস্ত সঙ্গী',
            'description'    => 'নিয়মিত ব্যবহারে শরীরের স্বাভাবিক সুস্থতা বজায় রাখতে সহায়ক এক মানসম্মত হারবাল সাপ্লিমেন্ট। স্বাস্থ্যকর জীবনযাপনের অংশ হিসেবে ব্যবহার করুন।',
            'price_label'    => 'আজকের বিশেষ মূল্য', 'price'          => 890, 'old_price'                       => 1200, 'save_text'                  => 'সাশ্রয় ৳৩১০',
            'primary_button' => 'এখনই Order করুন', 'secondary_button' => 'বিস্তারিত দেখুন', 'rating'            => '৫.০', 'rating_text'               => 'ক্রেতাদের সন্তুষ্ট অভিজ্ঞতা',
            'trust_title'    => 'নিরাপদ Order', 'trust_text'          => 'ক্যাশ অন Delivered', 'image_kicker'   => 'PREMIUM HERBAL WELLNESS',
            'image_note_top' => 'মানসম্মত', 'image_note_bottom'       => 'হারবাল সাপ্লিমেন্ট', 'delivery_title' => 'দ্রুত Delivered', 'delivery_text' => 'সারা বাংলাদেশে',
            'badge_one'      => 'ক্যাশ অন Delivered', 'badge_two'     => 'দ্রুত Delivered', 'badge_three'       => 'নিরাপদ প্যাকেজিং', 'badge_four'   => 'Customer সাপোর্ট',
        ] : [];
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

/* Legacy configuration below is intentionally inactive and retained temporarily.

        $reviewFields = ['eyebrow' => ['ছোট শিরোName', 'text'], 'title' => ['শিরোName', 'text'], 'description' => ['বর্ণনা', 'textarea']];
        $faqFields    = ['eyebrow' => ['ছোট শিরোName', 'text'], 'title' => ['শিরোName', 'text'], 'description' => ['বর্ণনা', 'textarea'], 'metric_one_value' => ['Metric ১ value', 'text'], 'metric_one_label' => ['Metric ১ label', 'text'], 'metric_two_value' => ['Metric ২ value', 'text'], 'metric_two_label' => ['Metric ২ label', 'text'], 'whatsapp_title' => ['WhatsApp title', 'text'], 'whatsapp_text' => ['WhatsApp description', 'text'], 'whatsapp_number' => ['WhatsApp number', 'text']];
        for ($i = 1; $i <= 5; $i++) {
            $faqFields += ["question_{$i}" => ["প্রশ্ন {$i}", 'text'], "answer_{$i}" => ["উত্তর {$i}", 'textarea']];
        }

        return [
            'general'      => ['label' => 'সাধারণ সেংস', 'fields' => ['site_title' => ['সাইটের Name', 'text'], 'seo_description' => ['SEO Description', 'textarea'], 'price' => ['অফার মূল্য', 'number'], 'old_price' => ['পুরাতন মূল্য', 'number'], 'whatsapp' => ['WhatsApp নম্বর', 'text']]],
            'seo'          => ['label' => 'SEO সেংস', 'image' => true, 'fields' => [
                'meta_title'          => ['Meta title', 'text'],
                'meta_description'    => ['Meta description', 'textarea'],
                'meta_keywords'       => ['Meta keywords', 'textarea'],
                'canonical_url'       => ['Canonical URL', 'text'],
                'robots'              => ['Robots (যেমন: index, follow)', 'text'],
                'og_title'            => ['Social share title', 'text'],
                'og_description'      => ['Social share description', 'textarea'],
                'schema_product_name' => ['Schema product name', 'text'],
                'schema_description'  => ['Schema product description', 'textarea'],
            ]],
            'hero'         => ['label' => 'Hero Section', 'image' => true, 'fields' => [
                'eyebrow'        => ['ছোট শিরোName', 'text'], 'title'                   => ['মূল শিরোName', 'text'], 'description'          => ['বর্ণনা', 'textarea'],
                'price_label'    => ['মূল্যের উপরের লেখা', 'text'], 'price'             => ['অফার মূল্য', 'number'], 'old_price'            => ['পুরাতন মূল্য', 'number'], 'save_text'     => ['সাশ্রয়ের লেখা', 'text'],
                'primary_button' => ['প্রধান button', 'text'], 'secondary_button'       => ['দ্বিতীয় button', 'text'], 'rating'             => ['Rating', 'text'], 'rating_text'           => ['Rating-এর নিচের লেখা', 'text'],
                'trust_title'    => ['Trust title', 'text'], 'trust_text'               => ['Trust description', 'text'], 'image_kicker'    => ['Image-এর উপরের লেখা', 'text'],
                'image_note_top' => ['Image badge (উপরে)', 'text'], 'image_note_bottom' => ['Image badge (নিচে)', 'text'], 'delivery_title' => ['Delivery title', 'text'], 'delivery_text' => ['Delivery description', 'text'],
                'badge_one'      => ['নিচের badge ১', 'text'], 'badge_two'              => ['নিচের badge ২', 'text'], 'badge_three'         => ['নিচের badge ৩', 'text'], 'badge_four'     => ['নিচের badge ৪', 'text'],
            ]],
            'why'          => ['label' => 'কেন Gavitral', 'fields' => [
                'eyebrow'          => ['ছোট শিরোName', 'text'], 'title'                  => ['শিরোName', 'text'], 'description'                => ['বর্ণনা', 'textarea'],
                'stat_one_value'   => ['Statistic ১ সংখ্যা', 'text'], 'stat_one_label'   => ['Statistic ১ লেখা', 'text'], 'stat_two_value'     => ['Statistic ২ সংখ্যা', 'text'], 'stat_two_label'  => ['Statistic ২ লেখা', 'text'],
                'stat_three_value' => ['Statistic ৩ সংখ্যা', 'text'], 'stat_three_label' => ['Statistic ৩ লেখা', 'text'], 'stat_four_value'    => ['Statistic ৪ সংখ্যা', 'text'], 'stat_four_label' => ['Statistic ৪ লেখা', 'text'],
                'card_one_icon'    => ['Card ১ icon class', 'text'], 'card_one_title'    => ['Card ১ title', 'text'], 'card_one_description'   => ['Card ১ description', 'textarea'],
                'card_two_icon'    => ['Card ২ icon class', 'text'], 'card_two_title'    => ['Card ২ title', 'text'], 'card_two_description'   => ['Card ২ description', 'textarea'],
                'card_three_icon'  => ['Card ৩ icon class', 'text'], 'card_three_title'  => ['Card ৩ title', 'text'], 'card_three_description' => ['Card ৩ description', 'textarea'],
                'card_four_icon'   => ['Card ৪ icon class', 'text'], 'card_four_title'   => ['Card ৪ title', 'text'], 'card_four_description'  => ['Card ৪ description', 'textarea'],
            ]],
            'benefits'     => ['label' => 'দৈনন্দিন উপকারিতা', 'fields' => [
                'eyebrow'         => ['ছোট শিরোName', 'text'], 'title'                 => ['শিরোName', 'text'], 'description'                => ['বর্ণনা', 'textarea'], 'note_title' => ['গুরুত্বপূর্ণ note title', 'text'], 'note_text' => ['গুরুত্বপূর্ণ note', 'textarea'],
                'card_one_icon'   => ['Card ১ icon class', 'text'], 'card_one_title'   => ['Card ১ title', 'text'], 'card_one_description'   => ['Card ১ description', 'textarea'],
                'card_two_icon'   => ['Card ২ icon class', 'text'], 'card_two_title'   => ['Card ২ title', 'text'], 'card_two_description'   => ['Card ২ description', 'textarea'],
                'card_three_icon' => ['Card ৩ icon class', 'text'], 'card_three_title' => ['Card ৩ title', 'text'], 'card_three_description' => ['Card ৩ description', 'textarea'],
                'card_four_icon'  => ['Card ৪ icon class', 'text'], 'card_four_title'  => ['Card ৪ title', 'text'], 'card_four_description'  => ['Card ৪ description', 'textarea'],
            ]],
            'features'     => ['label' => 'পণ্যের বৈশিষ্ট্য', 'fields' => [
                'eyebrow'         => ['ছোট শিরোName', 'text'], 'title'                 => ['শিরোName', 'text'], 'description'                => ['বর্ণনা', 'textarea'],
                'card_one_icon'   => ['Card ১ icon class', 'text'], 'card_one_title'   => ['Card ১ title', 'text'], 'card_one_description'   => ['Card ১ description', 'text'],
                'card_two_icon'   => ['Card ২ icon class', 'text'], 'card_two_title'   => ['Card ২ title', 'text'], 'card_two_description'   => ['Card ২ description', 'text'],
                'card_three_icon' => ['Card ৩ icon class', 'text'], 'card_three_title' => ['Card ৩ title', 'text'], 'card_three_description' => ['Card ৩ description', 'text'],
                'card_four_icon'  => ['Card ৪ icon class', 'text'], 'card_four_title'  => ['Card ৪ title', 'text'], 'card_four_description'  => ['Card ৪ description', 'text'],
                'card_five_icon'  => ['Card ৫ icon class', 'text'], 'card_five_title'  => ['Card ৫ title', 'text'], 'card_five_description'  => ['Card ৫ description', 'text'],
                'card_six_icon'   => ['Card ৬ icon class', 'text'], 'card_six_title'   => ['Card ৬ title', 'text'], 'card_six_description'   => ['Card ৬ description', 'text'],
            ]],
            'gallery'      => ['label' => 'পণ্য গ্যালারি', 'images' => 5, 'fields' => ['eyebrow' => ['ছোট শিরোName', 'text'], 'title' => ['শিরোName', 'text'], 'description' => ['বর্ণনা', 'textarea']]],
            'how-to-order' => ['label' => 'Orderের নিয়ম', 'fields' => [
                'eyebrow'           => ['ছোট শিরোName', 'text'], 'title'            => ['শিরোName', 'text'], 'description'               => ['বর্ণনা', 'textarea'],
                'step_one_number'   => ['Step ১ number', 'text'], 'step_one_icon'   => ['Step ১ icon class', 'text'], 'step_one_title'   => ['Step ১ title', 'text'], 'step_one_description'   => ['Step ১ description', 'textarea'],
                'step_two_number'   => ['Step ২ number', 'text'], 'step_two_icon'   => ['Step ২ icon class', 'text'], 'step_two_title'   => ['Step ২ title', 'text'], 'step_two_description'   => ['Step ২ description', 'textarea'],
                'step_three_number' => ['Step ৩ number', 'text'], 'step_three_icon' => ['Step ৩ icon class', 'text'], 'step_three_title' => ['Step ৩ title', 'text'], 'step_three_description' => ['Step ৩ description', 'textarea'],
            ]],
            'reviews'      => ['label' => 'ক্রেতাদের অভিজ্ঞতা', 'fields' => $reviewFields, 'dynamic_reviews' => true],
            'offer'        => ['label' => 'বিশেষ অফার', 'fields' => [
                'eyebrow' => ['Offer label', 'text'], 'title' => ['শিরোName', 'text'], 'description' => ['বর্ণনা', 'textarea'], 'old_price_label' => ['পুরাতন মূল্যের label', 'text'], 'old_price' => ['পুরাতন মূল্য', 'number'], 'price' => ['অফার মূল্য', 'number'], 'price_badge' => ['মূল্যের badge', 'text'], 'countdown_title' => ['Countdown title', 'text'], 'hours' => ['Countdown ঘণ্টা', 'number'], 'minutes' => ['Countdown মিনিট', 'number'], 'seconds' => ['Countdown সেকেন্ড', 'number'], 'button_text' => ['Button text', 'text'],
            ]],
            'faq'          => ['label' => 'FAQ', 'fields' => $faqFields],
            'order'        => ['label' => 'Order Form', 'image' => true, 'fields' => [
                'eyebrow'        => ['ছোট শিরোName', 'text'], 'title'              => ['শিরোName', 'text'], 'description'                  => ['বর্ণনা', 'textarea'], 'product_name'         => ['Product name', 'text'], 'price'            => ['অফার মূল্য', 'number'], 'old_price'        => ['পুরাতন মূল্য', 'number'],
                'benefit_one'    => ['সুবিধা ১', 'text'], 'benefit_two'            => ['সুবিধা ২', 'text'], 'benefit_three'                => ['সুবিধা ৩', 'text'], 'form_title'             => ['Form title', 'text'], 'form_description'   => ['Form description', 'textarea'],
                'name_label'     => ['Name label', 'text'], 'name_placeholder'     => ['Name placeholder', 'text'], 'phone_label'          => ['Phone label', 'text'], 'phone_placeholder'   => ['Phone placeholder', 'text'], 'email_label' => ['Email label', 'text'], 'email_placeholder' => ['Email placeholder', 'text'], 'address_label' => ['Address label', 'text'], 'address_placeholder' => ['Address placeholder', 'text'],
                'quantity_label' => ['Quantity label', 'text'], 'minimum_quantity' => ['সর্বনিম্ন quantity', 'number'], 'maximum_quantity' => ['সর্বোচ্চ quantity', 'number'], 'total_label' => ['Total label', 'text'], 'total_note'        => ['Total note', 'text'], 'button_text'        => ['Button text', 'text'], 'privacy_text'        => ['Privacy text', 'text'],
            ]],
            'footer'       => ['label' => 'Footer', 'fields' => [
                'badge_one_icon'   => ['Badge ১ icon class', 'text'], 'badge_one_title'         => ['Badge ১ title', 'text'], 'badge_one_text'   => ['Badge ১ subtitle', 'text'],
                'badge_two_icon'   => ['Badge ২ icon class', 'text'], 'badge_two
*/
