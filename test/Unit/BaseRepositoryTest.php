<?php

namespace Gblix\Tests\Unit;

use Gblix\Tests\TestCase;
use Illuminate\Support\LazyCollection;

final class BaseRepositoryTest extends TestCase
{
    private RepositoryStub $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
        $this->repository = $this->app->make(RepositoryStub::class);
    }

    private function seedModels(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            ModelStub::query()->create(['name' => 'Model ' . $i]);
        }
    }

    public function testExists(): void
    {
        $this->seedModels(1);

        $this->assertTrue($this->repository->exists(1));
        $this->assertFalse($this->repository->exists(999));
    }

    public function testCursor(): void
    {
        $this->seedModels(3);

        $results = $this->repository->cursor();

        $this->assertInstanceOf(LazyCollection::class, $results);
        $this->assertCount(3, $results);
        $this->assertInstanceOf(ModelStub::class, $results->first());
    }

    public function testFindReturnsPresentedResult(): void
    {
        $this->seedModels(1);

        $result = $this->repository->find(1);

        $this->assertIsArray($result);
        $this->assertSame('Model 1', $result['data']['name']);
    }

    public function testPaginateNoLimitReturnsPresentedPagination(): void
    {
        $this->seedModels(3);

        $result = $this->repository->paginateNoLimit();

        $this->assertIsArray($result);
        $this->assertCount(0, $result['data']);
        $this->assertSame(3, $result['meta']['pagination']['total']);
    }

    public function testPaginateAllReturnsPresentedPagination(): void
    {
        $this->seedModels(3);

        $result = $this->repository->paginateAll();

        $this->assertIsArray($result);
        $this->assertCount(3, $result['data']);
        $this->assertSame(3, $result['meta']['pagination']['total']);
    }

    public function testCollectionPresenterDefaultsToNull(): void
    {
        $this->assertNull($this->repository->collectionPresenter());
    }
}
