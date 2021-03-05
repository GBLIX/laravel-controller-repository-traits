<?php

namespace Gblix\Repository\Contracts;

use Prettus\Repository\Contracts\Presentable;
use Prettus\Repository\Contracts\RepositoryCriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface as BaseRepositoryInterface;

interface RepositoryInterface extends BaseRepositoryInterface, Presentable, RepositoryCriteriaInterface
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model();

    /**
     * Specify the Presenter class name used on Collections
     *
     * @return string|null
     */
    public function collectionPresenter();

    /**
     * Retrieve no data from repository, but wtih paginate meta
     *
     * @return mixed
     */
    public function paginateNoLimit();

    /**
     * Retrieve all data of repository, but wtih paginate meta
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function paginateAll($columns = ['*']);

    /**
     * Wrapper result data
     *
     * @param mixed $result
     *
     * @return mixed
     */
    public function parserResult($result);
}
