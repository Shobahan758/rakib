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
}
