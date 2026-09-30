<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class BladeTemplateSyntaxTest extends TestCase
{
    #[DataProvider('bladeTemplates')]
    public function test_templates_do_not_use_the_fragile_inline_php_directive(string $template): void
    {
        $source = file_get_contents($template);

        $this->assertStringNotContainsString(
            '@php(',
            $source,
            "Use an @php / @endphp block in {$template}; inline directives can compile to invalid PHP.",
        );
    }

    public static function bladeTemplates(): iterable
    {
        $root = dirname(__DIR__, 2).'/resources/views';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($files as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                yield $file->getPathname() => [$file->getPathname()];
            }
        }
    }
}
