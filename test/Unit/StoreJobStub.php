<?php

namespace Gblix\Tests\Unit;

final class StoreJobStub
{
    public function run(): void
    {
    }

    public function handle(array $data): ModelStub
    {
        return ModelStub::query()->create($data);
    }
}
