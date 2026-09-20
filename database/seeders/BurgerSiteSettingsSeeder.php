<?php

namespace Database\Seeders;

use App\Models\LandingSection;
use Illuminate\Database\Seeder;

class BurgerSiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = LandingSection::definitions();

        LandingSection::whereNotIn('slug', array_keys($definitions))->delete();

        foreach (array_keys($definitions) as $slug) {
            LandingSection::firstOrCreate(
                ['slug' => $slug],
                ['content' => LandingSection::defaults($slug), 'is_visible' => true],
            );
        }
    }
}
