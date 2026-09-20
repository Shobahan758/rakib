<?php

namespace Tests\Unit;

use App\Models\LandingSection;
use PHPUnit\Framework\TestCase;

class FrontendContentMappingTest extends TestCase
{
    public function test_every_default_content_value_has_a_backend_editor_field(): void
    {
        $definitions = LandingSection::definitions();

        foreach ($definitions as $slug => $definition) {
            $missing = array_diff(
                array_keys(LandingSection::defaults($slug)),
                array_keys($definition['fields']),
            );

            $this->assertSame([], array_values($missing), "{$slug} has frontend content without an editor field.");
        }
    }

    public function test_literal_frontend_content_references_are_backed_by_editor_fields(): void
    {
        $definitions = LandingSection::definitions();
        $templates = glob(dirname(__DIR__, 2).'/resources/views/landing/*.blade.php') ?: [];
        $references = [];

        foreach ($templates as $template) {
            $source = file_get_contents($template);
            preg_match_all("/\\\$content\\('([^']+)',\\s*'([^']+)'\\)/", $source, $matches, PREG_SET_ORDER);
            array_push($references, ...$matches);
        }

        $this->assertNotEmpty($references);
        foreach ($references as $reference) {
            [, $slug, $key] = $reference;
            $this->assertArrayHasKey($slug, $definitions, "Unknown frontend section: {$slug}");
            $this->assertArrayHasKey($key, $definitions[$slug]['fields'], "{$slug}.{$key} is not editable in the backend.");
        }
    }
}
