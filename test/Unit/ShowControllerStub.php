<?php

namespace Gblix\Tests\Unit;

use Gblix\Controllers\ApiTraits\Read;
use Gblix\Controllers\ApiTraits\Show;
use Illuminate\Routing\Controller;

final class ShowControllerStub extends Controller
{
    use Read;
    use Show;

    protected RepositoryStub $repository;

    public function __construct(RepositoryStub $repository)
    {
        $this->repository = $repository;
    }
}
