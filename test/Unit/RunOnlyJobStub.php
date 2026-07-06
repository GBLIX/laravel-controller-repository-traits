<?php

namespace Gblix\Tests\Unit;

/**
 * A plain job exposing only run() — dispatched when the job has no handle().
 */
final class RunOnlyJobStub
{
    public function run(array $data): ModelStub
    {
        return ModelStub::query()->create($data);
    }
}
