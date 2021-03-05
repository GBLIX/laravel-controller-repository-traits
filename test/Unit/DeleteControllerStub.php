<?php

namespace Gblix\Tests\Unit;

use Gblix\Controllers\ApiTraits\Delete;
use Gblix\Controllers\ApiTraits\Read;
use Illuminate\Routing\Controller;

final class DeleteControllerStub extends Controller
{
    use Read;
    use Delete;

    protected RepositoryStub $repository;

    public function __construct(RepositoryStub $repository)
    {
        $this->repository = $repository;
    }
}
