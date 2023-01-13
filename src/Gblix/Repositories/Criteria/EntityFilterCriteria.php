<?php

namespace Gblix\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class EntityFilterCriteria
 * @package App\Repositories\Criteria
 */
class EntityFilterCriteria implements CriteriaInterface
{
    /**
     * @var array|null
     */
    protected $data;

    /**
     * EntityFilterCriteria constructor.
     * @param array|null $data
     */
    public function __construct(?array $data)
    {
        $this->data = $data;
    }

    /**
     * Apply criteria in query repository
     *
     * @param Builder|mixed $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $model = $model->filter($this->data);

        return $model;
    }
}
