<?php

namespace Tests\Unit;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminPaginationViewTest extends TestCase
{
    public function test_pagination_appears_after_fifteen_items(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 15), 31, 15, 1, [
            'path' => 'https://example.test/admin/orders/today',
        ]);

        $html = $paginator->links()->render();

        $this->assertStringContainsString('Showing 1–15 of 31', $html);
        $this->assertStringContainsString('?page=2', $html);
        $this->assertStringContainsString('pagination-link active', $html);
    }

    public function test_pagination_stays_hidden_for_fifteen_items_or_fewer(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 15), 15, 15, 1);

        $this->assertSame('', trim($paginator->links()->render()));
    }
}
