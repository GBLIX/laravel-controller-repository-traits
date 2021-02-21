<?php

namespace Gblix\Controllers\ApiTraits;

use Clockwork\Clockwork;
use Gblix\Repository\Contracts\NegociatesPresenterContentInterface;
use Gblix\Repository\Contracts\RepositoryInterface;
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
     * @return array|null
     */
    final protected function makeStore(Request $request, $job, RepositoryInterface $repository): ?array
    {
        /* @var $clockwork Clockwork */
        $clockwork = clock();

        $clockwork->event($clockworkEvent = 'Dispatching store on controller')->begin();

        $data = $request->except(array_keys($request->query()));

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
     * @param Request $request
     * @param RepositoryInterface $repository
     * @return void
     */
    final protected function prepareStore(Request $request, RepositoryInterface $repository): void
    {
        $repository->skipPresenter(false);

        $presenter = $this->getStorePresenter();
        if ($presenter) {
            $repository->setPresenter($presenter);
        }

        if ($repository instanceof NegociatesPresenterContentInterface) {
            $repository->negociatesPresenterContent($request);
        }
    }

    /**
     * Returns the data for store methods
     *
     * @param array|null $data
     * @return \Illuminate\Http\Response
     */
    final protected function makeStoreResponse(?array $data): Response
    {
        return response()->json($data, 201);
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
