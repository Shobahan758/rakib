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
}
