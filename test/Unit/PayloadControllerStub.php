<?php

namespace Gblix\Tests\Unit;

use Gblix\Controllers\ApiTraits\Create;
use Gblix\Controllers\ApiTraits\Delete;
use Gblix\Controllers\ApiTraits\Read;
use Gblix\Controllers\ApiTraits\Update;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

final class PayloadControllerStub extends Controller
{
    use Read;
    use Create;
    use Update;
    use Delete;

    protected RepositoryStub $repository;

    public function __construct(RepositoryStub $repository)
    {
        $this->repository = $repository;
    }

    public function store(Request $request): Response
    {
        return $this->runStore($request, new PayloadRecordingJobStub());
    }

    public function update(Request $request): Response
    {
        return $this->runUpdate($request, new PayloadRecordingJobStub());
    }

    public function destroy(Request $request): Response
    {
        return $this->runDestroy($request, new PayloadRecordingJobStub());
    }
}
