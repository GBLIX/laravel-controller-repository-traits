<?php

namespace Gblix\Tests\Unit;

use Gblix\Tests\TestCase;
use Illuminate\Support\Facades\Route;

final class ApiTraitsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();

        Route::get('/stubs', [RetrieveControllerStub::class, 'index']);
        Route::post('/stubs', [CreateControllerStub::class, 'store']);
        Route::get('/stubs/{id}', [ShowControllerStub::class, 'show']);
        Route::patch('/stubs/{id}', [UpdateControllerStub::class, 'update']);
        Route::delete('/stubs/{id}', [DeleteControllerStub::class, 'destroy']);
    }

    private function seedModels(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            ModelStub::query()->create(['name' => 'Model ' . $i]);
        }
    }

    public function testIndexReturnsPaginatedPresentedData(): void
    {
        $this->seedModels(3);

        $this->getJson('/stubs')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.pagination.total', 3);
    }

    public function testIndexWithLimitZeroUsesPaginateNoLimit(): void
    {
        $this->seedModels(3);

        $this->getJson('/stubs?limit=0')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.pagination.total', 3);
    }

    public function testIndexWithLimitMinusOneUsesPaginateAll(): void
    {
        $this->seedModels(3);

        $this->getJson('/stubs?limit=-1')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.pagination.total', 3);
    }

    public function testIndexAppliesEntityFilterCriteria(): void
    {
        $this->seedModels(3);

        $this->getJson('/stubs?name=Model 2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Model 2');
    }

    public function testShowReturnsPresentedItem(): void
    {
        $this->seedModels(2);

        $this->getJson('/stubs/2')
            ->assertOk()
            ->assertJsonPath('data.name', 'Model 2');
    }

    public function testStoreCreatesAndReturnsPresentedItem(): void
    {
        $this->postJson('/stubs', ['name' => 'Created'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Created');

        $this->assertSame(1, ModelStub::query()->where('name', 'Created')->count());
    }

    public function testUpdateChangesAndReturnsPresentedItem(): void
    {
        $this->seedModels(1);

        $this->patchJson('/stubs/1', ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->assertSame('Renamed', ModelStub::query()->findOrFail(1)->getAttribute('name'));
    }

    public function testDestroyDeletesAndReturnsNoContent(): void
    {
        $this->seedModels(1);

        $this->deleteJson('/stubs/1')->assertNoContent();

        $this->assertSame(0, ModelStub::query()->count());
    }
}
