<?php

namespace Gblix\Repository;

use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Http\Request;
use Prettus\Repository\Eloquent\BaseRepository as PrettusBaseRepository;
use Prettus\Validator\Contracts\ValidatorInterface;

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
     * {@inheritDoc}
     * @return string|null
     */
    public function validator()
    {
        return parent::validator();
    }

    /**
     * {@inheritDoc}
     * @param ValidatorInterface|string|null $validator
     * @return ValidatorInterface|null
     */
    public function makeValidator($validator = null)
    {
        return parent::makeValidator($validator);
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
