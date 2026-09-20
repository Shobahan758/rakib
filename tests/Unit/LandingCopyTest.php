<?php

namespace Tests\Unit;

use App\Models\LandingSection;
use Tests\TestCase;

class LandingCopyTest extends TestCase
{
    public function test_default_landing_copy_has_no_legacy_burger_wording(): void
    {
        foreach (array_keys(LandingSection::definitions()) as $slug) {
            $content = json_encode(LandingSection::defaults($slug), JSON_UNESCAPED_UNICODE);

            $this->assertIsString($content);
            $this->assertDoesNotMatchRegularExpression('/burger|বার্গার/ui', $content, $slug);
        }
    }

    public function test_hero_has_the_furniture_polish_offer_below_its_image(): void
    {
        $hero = LandingSection::defaults('hero');

        $this->assertSame('Solution Mart-এর Furniture Polish Combo—১৩৫০ টাকার প্যাকেজ', $hero['offer_text']);
        $this->assertSame('এখন মাত্র ৯৯০ টাকা। সারা দেশে ক্যাশ অন ডেলিভারি।', $hero['offer_highlight']);
    }

    public function test_complete_care_has_the_one_year_brightness_guarantee(): void
    {
        $completeCare = LandingSection::defaults('complete_care');

        $this->assertSame('✨ ১ বছরের উজ্জ্বলতা গ্যারান্টি!', $completeCare['guarantee_title']);
        $this->assertSame('১ বছরের মধ্যে উজ্জ্বলতা নষ্ট হলে—পাবেন আরও ১ বোতল পলিশ সম্পূর্ণ ফ্রি! 🎁', $completeCare['guarantee_text']);
        $this->assertSame('উজ্জ্বলতার নিশ্চয়তা, লিখিত গ্যারান্টিতে।', $completeCare['guarantee_note']);
    }

    public function test_every_frontend_copy_control_is_exposed_in_the_admin_definitions(): void
    {
        foreach (LandingSection::definitions() as $slug => $definition) {
            foreach (LandingSection::defaults($slug) as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                $this->assertArrayHasKey($key, $definition['fields'], "{$slug}.{$key} is not editable");
            }
        }

        $this->assertArrayHasKey('card_1_icon', LandingSection::definitions()['features']['fields']);
        $this->assertArrayHasKey('regular_price_label', LandingSection::definitions()['order']['fields']);
        $this->assertArrayHasKey('sound_enabled_label', LandingSection::definitions()['video']['fields']);
    }
}
