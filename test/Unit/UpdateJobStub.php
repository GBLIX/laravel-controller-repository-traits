<?php

namespace Gblix\Tests\Unit;

final class UpdateJobStub
{
    public function run(): void
    {
    }

    public function handle(array $data): ModelStub
    {
        /** @var ModelStub $model */
        $model = ModelStub::query()->findOrFail($data['id']);
        $model->fill(['name' => $data['name']]);
        $model->save();

        return $model;
    }
}
