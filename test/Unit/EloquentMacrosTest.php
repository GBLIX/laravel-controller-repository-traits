<?php

namespace Gblix\Tests\Unit;

use Gblix\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentMacrosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    private function seedModels(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            ModelStub::query()->create(['name' => 'Model ' . $i]);
        }
    }

    public function testPaginateNoLimitReturnsEmptyItemsWithFullPaginationMetadata(): void
    {
        $this->seedModels(3);

        $result = ModelStub::query()->paginateNoLimit();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame(3, $result->total());
        $this->assertCount(0, $result->items());
    }

    public function testPaginateAllReturnsAllItemsWithFullPaginationMetadata(): void
    {
        $this->seedModels(3);

        $result = ModelStub::query()->paginateAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame(3, $result->total());
        $this->assertCount(3, $result->items());
    }

    public function testPaginateAllOnEmptyTableReturnsEmptyPagination(): void
    {
        $result = ModelStub::query()->paginateAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame(0, $result->total());
        $this->assertCount(0, $result->items());
    }
}
