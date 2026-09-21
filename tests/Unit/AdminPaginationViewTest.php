<?php

namespace Tests\Unit;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminPaginationViewTest extends TestCase
{
    public function test_pagination_appears_after_ten_items(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 10), 21, 10, 1, [
            'path' => 'https://example.test/admin/orders/today',
        ]);

        $html = $paginator->links()->render();

        $this->assertStringContainsString('Showing 1–10 of 21', $html);
        $this->assertStringContainsString('?page=2', $html);
        $this->assertStringContainsString('pagination-link active', $html);
    }

    public function test_pagination_stays_hidden_for_ten_items_or_fewer(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 10), 10, 10, 1);

        $this->assertSame('', trim($paginator->links()->render()));
    }
}
