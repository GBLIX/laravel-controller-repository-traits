<?php

namespace Gblix\Tests\Unit;

use Gblix\Tests\TestCase;
use League\Fractal\Manager;

final class FractalPresenterTest extends TestCase
{
    private PresenterStub $presenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
        $this->presenter = $this->app->make(PresenterStub::class);
    }

    public function testPresentItem(): void
    {
        $model = ModelStub::query()->create(['name' => 'Alice']);

        $result = $this->presenter->present($model);

        $this->assertSame('Alice', $result['data']['name']);
    }

    public function testPresentCollection(): void
    {
        ModelStub::query()->create(['name' => 'Alice']);
        ModelStub::query()->create(['name' => 'Bob']);

        $result = $this->presenter->present(ModelStub::query()->get());

        $this->assertCount(2, $result['data']);
    }

    public function testPresentPaginator(): void
    {
        ModelStub::query()->create(['name' => 'Alice']);
        ModelStub::query()->create(['name' => 'Bob']);

        $result = $this->presenter->present(ModelStub::query()->paginate(1));

        $this->assertCount(1, $result['data']);
        $this->assertSame(2, $result['meta']['pagination']['total']);
    }

    public function testGetFractalReturnsManager(): void
    {
        $this->assertInstanceOf(Manager::class, $this->presenter->getFractal());
    }
}
