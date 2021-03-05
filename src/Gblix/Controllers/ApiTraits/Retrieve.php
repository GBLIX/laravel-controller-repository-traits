<?php

namespace Gblix\Controllers\ApiTraits;

use Clockwork\Clockwork;
use Gblix\Repositories\Criteria\EntityFilterCriteria;
use Gblix\Repository\Contracts\NegociatesPresenterContentInterface;
use Gblix\Repository\Contracts\RepositoryInterface;
use GrahamCampbell\Binput\Binput;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait Retrieve
 * @package Gblix\Controllers\ApiTraits
 *
 * @property RepositoryInterface $repository
 */
trait Retrieve
{

    /**
     * Default action for GET (INDEX) methods
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Throwable
     */
    public function index(Request $request): Response
    {
        return $this->runIndex($request, $this->repository);
    }

    /**
     * Default runner for GET (INDEX) methods
     *
     * @param Request $request
     * @param RepositoryInterface $repository
     *
     * @return Response
     * @throws \Throwable
     */
    public function runIndex(Request $request, RepositoryInterface $repository): Response
    {

        /* @var $clockwork Clockwork */
        $clockwork = clock();

        $clockwork->event($clockworkEvent = 'Running index action on controller')->begin();

        $data = $this->makeIndex($request, $repository);

        $reponse = $this->makeIndexResponse($data);

        $clockwork->event($clockworkEvent)->end();

        return $reponse;
    }

    /**
     * Perfoms the data for retrieve methods
     *
     * @param Request $request
     * @param RepositoryInterface $repository
     *
     * @return mixed
     * @throws \Throwable
     */
    protected function makeIndex(Request $request, RepositoryInterface $repository)
    {

        $binput = $this->makeIndexBinput($request);

        $repository->resetCriteria();

        $repository = $this->pushEntityRelations($repository, $request);

        //Can use entity filter
        if (method_exists($repository->model(), 'scopeFilter')) {
            $repository = $repository = $this->pushEntityFilterCriteria($repository, $binput);
        }

        //Not applied for now
        // $repository->pushCriteria(new \Prettus\Repository\Criteria\RequestCriteria($request));

        if (method_exists($this, 'pushReadCriteria')) {
            $this->pushReadCriteria($repository);
        }

        $repository = $this->pushIndexCriteria($repository, $request);

        $this->prepareRetrieve($request, $repository);

        $limit = $binput->input('limit');

        // Se não foi definido limite e não precisa ser paginado: Exibimos tudo
        if ($limit === null && !$this->willIndexPaginate()) {
            $result = $repository->all();
        } else {
            $limit = is_numeric($limit) ? (int)$limit : $limit;
            if ($limit === 0) {
                $result = $repository->paginateNoLimit();
            } elseif ($limit === -1) {
                $result = $repository->paginateAll();
            } else {
                $result = $repository->paginate($limit);
            }
        }

        $repository->resetCriteria();

        return $result;
    }

    /**
     * Prepares the repository based on request received
     *
     * @param Request $request
     * @param RepositoryInterface $repository
     * @return void
     */
    final protected function prepareRetrieve(Request $request, RepositoryInterface $repository): void
    {
        $repository->skipPresenter(false);

        $presenter = $this->getRetrievePresenter();
        if ($presenter === null) {
            $presenter = $repository->collectionPresenter();
        }
        if ($presenter !== null) {
            $repository->setPresenter($presenter);
        }

        if ($repository instanceof NegociatesPresenterContentInterface) {
            $repository->negociatesPresenterContent($request);
        }
    }

    /**
     * Returns if the data for retrieve methods will be paginated
     *
     * @return bool
     */
    protected function willIndexPaginate(): bool
    {
        return true;
    }


    /**
     * @param Request $request
     * @return Binput
     */
    protected function makeIndexBinput(Request $request): Binput
    {
        $binput = app('binput');
        $binput->setRequest($request);
        return $binput;
    }

    /**
     * @param Binput $binput
     * @param array $data
     * @return array
     */
    protected function filterRequestToEntityFilter(Binput $binput, array $data): array
    {
        return $data;
    }

    /**
     * Add Entity Filter Criteria to index
     *
     * @param RepositoryInterface $repository
     * @param Binput $binput
     *
     * @return RepositoryInterface
     */
    protected function pushEntityFilterCriteria(RepositoryInterface $repository, Binput $binput): RepositoryInterface
    {
        $filter = $binput->getRequest()->input('filter', []);
        if (is_string($filter)) {
            $filter = json_decode($filter, true, 5, JSON_THROW_ON_ERROR);
        }

        if (is_array($filter)) {
            $nullValues = array_filter($filter, static function ($value): bool {
                return $value === null;
            });

            $data = $binput->clean($filter);

            foreach ($nullValues as $key => $value) {
                $data[$key] = $value;
            }
        } else {
            $data = $filter;
        }

        $data = $this->filterRequestToEntityFilter($binput, $data);
        $repository->pushCriteria(new EntityFilterCriteria($data));

        return $repository;
    }

    /**
     * Push Eager loads to repository
     *
     * @param RepositoryInterface $repository
     * @param Request $request
     * @return RepositoryInterface
     */
    protected function pushEntityRelations(RepositoryInterface $repository, Request $request): RepositoryInterface
    {
        return $repository;
    }

    /**
     * Return the presenter used for retrieve methods - defaults to single
     * @return string
     */
    protected function getRetrievePresenter(): ?string
    {
        return null;
    }

    /**
     * Send additional criteria to index
     *
     * @param RepositoryInterface $repository
     * @param Request $request
     *
     * @return RepositoryInterface
     */
    protected function pushIndexCriteria(RepositoryInterface $repository, Request $request): RepositoryInterface
    {
        return $repository;
    }

    /**
     * Returns the data for retrieve methods
     *
     * @param mixed $data
     * @return Response
     */
    protected function makeIndexResponse($data): Response
    {
        /** @var ResponseFactory $response */
        $response = response();
        return $response->json($data);
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
    final protected function retrieveResponse(Request $request, RepositoryInterface $repository, $data): Response
    {
        $this->prepareRetrieve($request, $repository);
        return $this->makeIndexResponse($data);
    }
}
