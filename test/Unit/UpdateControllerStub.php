<?php

namespace Gblix\Tests\Unit;

use Gblix\Controllers\ApiTraits\Read;
use Gblix\Controllers\ApiTraits\Update;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

final class UpdateControllerStub extends Controller
{
    use Read;
    use Update;

    protected RepositoryStub $repository;

    public function __construct(RepositoryStub $repository)
    {
        $this->repository = $repository;
    }

    public function update(Request $request): Response
    {
        return $this->makeUpdateResponse([]);
    }
}
