<?php

namespace Gblix\Tests\Unit;

use Gblix\Controllers\ApiTraits\Retrieve;
use Illuminate\Routing\Controller;

final class RetrieveControllerStub extends Controller
{
    use Retrieve;

    protected RepositoryStub $repository;

    public function __construct(RepositoryStub $repository)
    {
        $this->repository = $repository;
    }
}
