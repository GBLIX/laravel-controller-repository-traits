<?php

namespace Gblix\Controllers\ApiTraits;

use Clockwork\Clockwork;
use Gblix\Repository\Contracts\NegociatesPresenterContentInterface;
use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Action;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait Create
 * @package Gblix\Controllers\ApiTraits
 *
 * @property RepositoryInterface $repository
 */
trait Create
{

    /**
     * Default action POST (STORE) methods
     *
     * @param Request $request
     * @return Response
     */
    abstract public function store(Request $request): Response;

    /**
     * Default runner action for POST (STORE) methods
     *
     * @param Request $request
     * @param Action|string $job
     * @return Response
     * @throws \Throwable
     */
    final public function runStore(Request $request, $job): Response
    {
        $model = $this->makeStore($request, $job, $this->repository);
        return $this->makeStoreResponse($model);
    }

    /**
     * Perfoms the data for store methods
     *
     * @param Request $request
     * @param Action|string $job
     * @param RepositoryInterface $repository
     * @return mixed
     */
    final protected function makeStore(Request $request, $job, RepositoryInterface $repository)
    {
        /* @var $clockwork Clockwork */
        $clockwork = clock();

        $clockwork->event($clockworkEvent = 'Dispatching store on controller')->begin();

        $query = $request->query();
        assert(is_array($query));
        $data = $request->except(array_keys($query));

        $user = $request->user();

        if (!$job instanceof Action) {
            $job = new $job();
        }

        $result = $job->actingAs($user)
            ->run($data);

        $clockwork->event($clockworkEvent)->end();

        if (!$result) {
            return $result;
        }

        $clockwork->event($clockworkEvent = 'Parsing store response on controller')->begin();

        $this->prepareStore($request, $repository);

        $result = $repository->parserResult($result);

        $clockwork->event($clockworkEvent)->end();

        return $result;
    }

    /**
     * Prepares the repository based on request received
     *
     * @param Request $request
     * @param RepositoryInterface $repository
     * @return void
     */
    final protected function prepareStore(Request $request, RepositoryInterface $repository): void
    {
        $repository->skipPresenter(false);

        $presenter = $this->getStorePresenter();
        if ($presenter !== null) {
            $repository->setPresenter($presenter);
        }

        if ($repository instanceof NegociatesPresenterContentInterface) {
            $repository->negociatesPresenterContent($request);
        }
    }

    /**
     * Returns the data for store methods
     *
     * @param mixed $data
     * @return Response
     */
    final protected function makeStoreResponse($data): Response
    {
        /** @var ResponseFactory $response */
        $response = response();
        return $response->json($data, 201);
    }

    /**
     * Prepares the repository based on request received and return the response
     * with the given data
     *
     * @param Request $request
     * @param RepositoryInterface $repository
     * @param mixed $data
     * @return Response
     */
    final protected function storeResponse(Request $request, RepositoryInterface $repository, $data): Response
    {
        $this->prepareStore($request, $repository);
        $data = $repository->parserResult($data);
        return $this->makeStoreResponse($data);
    }

    /**
     * Return the presenter used for retrieve methods - defaults to single
     * @return string
     */
    protected function getStorePresenter(): ?string
    {
        return null;
    }
}
