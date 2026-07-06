<?php

namespace Gblix\Tests\Unit;

/**
 * A plain job exposing only handle() — the form the README advertises for plain classes.
 */
final class HandleOnlyJobStub
{
    public function handle(array $data): ModelStub
    {
        return ModelStub::query()->create($data);
    }
}
