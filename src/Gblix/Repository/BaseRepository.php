<?php

namespace Gblix\Repository;

use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Http\Request;
use Prettus\Repository\Eloquent\BaseRepository as PrettusBaseRepository;

/**
 * Class BaseRepository
 * @package Gblix\Repository
 */
abstract class BaseRepository extends PrettusBaseRepository implements RepositoryInterface
{
    /**
     * Exists the data id
     *
     * @param mixed $id
     *
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function exists($id): bool
    {
        $this->applyCriteria();
        $this->applyScope();

        $exists = !is_null($this->model->find($id));

        $this->resetModel();

        return $exists;
    }

    /**
     * Retrieve all data of repository
     *
     * @return mixed
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function cursor()
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->cursor();

        $this->resetModel();
        $this->resetScope();

        return $results;
    }

    /**
     * Specify the Presenter class name used on Collections
     *
     * @return string|null
     */
    public function collectionPresenter()
    {
        return null;
    }

    /**
     * Retrieve all data of repository, paginated
     *
     * @return mixed
     */
    public function paginateNoLimit()
    {
        /** @var Request $request */
        $request = app('request');

        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->paginateNoLimit();
        $results->appends($request->query());

        $this->resetModel();

        return $this->parserResult($results);
    }

    /**
     * Retrieve all data of repository, paginated
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function paginateAll($columns = ['*'])
    {
        /** @var Request $request */
        $request = app('request');

        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->paginateAll($columns);
        $results->appends($request->query());

        $this->resetModel();

        return $this->parserResult($results);
    }
}
