<?php

namespace Gblix\Controllers\ApiTraits;

use Clockwork\Clockwork;
use Gblix\Repository\Contracts\NegociatesPresenterContentInterface;
use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\RepositoryCriteriaInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait Show
 * @package Gblix\Controllers\ApiTraits
 *
 * @property RepositoryInterface $repository
 *
 * @mixin Read
 */
trait Show
{

    /**
     * Default action for GET (SHOW) methods
     *
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        return $this->runShow($request);
    }

    /**
     * Default runner for GET (SHOW) methods
     *
     * @param Request $request
     * @param string|int|null $id
     * @return Response
     */
    final public function runShow(Request $request, $id = null): Response
    {
        /* @var $clockwork Clockwork */
        $clockwork = clock();

        $clockwork->event($clockworkEvent = 'Running show action on controller')->begin();

        $id = $id ?? $this->getCurrentEntryId($request);

        $data = $this->makeShow($request, $this->repository, $id);

        $response = $this->makeShowResponse($data);

        $clockwork->event($clockworkEvent)->end();

        return $response;
    }

    /**
     * Perfoms the data for show methods
     *
     * @param Request $request
     * @param RepositoryInterface|RepositoryCriteriaInterface $repository
     * @param string|int $id
     * @return array
     */
    final protected function makeShow(Request $request, RepositoryInterface $repository, $id): array
    {
        $repository->resetCriteria();
        $this->pushReadCriteria($repository);
        $this->pushShowCriteria($repository);

        $this->prepareShow($request, $repository);

        $result = $repository->find($id);

        $repository->resetCriteria();

        return $result;
    }

    /**
     * @param Request $request
     * @param RepositoryInterface $repository
     * @return void
     */
    final protected function prepareShow(Request $request, RepositoryInterface $repository): void
    {
        $repository->skipPresenter(false);

        $presenter = $this->getShowPresenter();
        if ($presenter) {
            $repository->setPresenter($presenter);
        }

        if ($repository instanceof NegociatesPresenterContentInterface) {
            $repository->negociatesPresenterContent($request);
        }
    }

    /**
     * Send additional criteria to show
     *
     * @param RepositoryInterface $repository
     *
     * @return void
     */
    protected function pushShowCriteria(RepositoryInterface $repository): void
    {
    }

    /**
     * Return the presenter used for show methods - defaults to single
     * @return string
     */
    protected function getShowPresenter(): ?string
    {
        return null;
    }

    /**
     * Returns the data for show methods
     *
     * @param array $data
     * @return Response
     */
    final protected function makeShowResponse(array $data): Response
    {
        return response()->json($data);
    }
}
