<?php

namespace Gblix\Tests\Unit;

use Gblix\Controllers\ApiTraits\Create;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

final class CreateControllerStub extends Controller
{
    use Create;

    protected RepositoryStub $repository;

    public function __construct(RepositoryStub $repository)
    {
        $this->repository = $repository;
    }

    public function store(Request $request): Response
    {
        return $this->makeStoreResponse([]);
    }
}
