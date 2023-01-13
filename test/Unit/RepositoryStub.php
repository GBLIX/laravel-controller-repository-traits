<?php

namespace Gblix\Tests\Unit;

use Gblix\Repository\BaseRepository;
use Illuminate\Database\Eloquent\Model;

final class RepositoryStub extends BaseRepository
{
    public function model()
    {
        return Model::class;
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
