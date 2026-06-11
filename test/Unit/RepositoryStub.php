<?php

namespace Gblix\Tests\Unit;

use Gblix\Repository\BaseRepository;

final class RepositoryStub extends BaseRepository
{
    public function model()
    {
        return ModelStub::class;
    }

    public function presenter()
    {
        return PresenterStub::class;
    }

    public function validator()
    {
        return ValidatorStub::class;
    }
}
