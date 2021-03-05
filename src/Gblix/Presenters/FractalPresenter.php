<?php

namespace Gblix\Presenters;

use Gblix\Presenters\Contracts\PresenterInterface;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalAlias;
use League\Fractal\Resource\ResourceInterface;
use Prettus\Repository\Presenter\FractalPresenter as BaseFractalPresenter;

abstract class FractalPresenter extends BaseFractalPresenter implements PresenterInterface
{

    /**
     * @var ResourceInterface|null
     */
    protected $resource;

    /**
     * @param mixed $data
     * @return array|null
     */
    public function present($data)
    {
        $this->resource($data);
        if ($this->resource === null) {
            return null;
        }

        return $this->fractal->createData($this->resource)->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getFractal(): Manager
    {
        return $this->fractal;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceKeyItem(): string
    {
        return $this->resourceKeyItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceKeyCollection(): string
    {
        return $this->resourceKeyCollection;
    }

    /**
     * @param mixed $data
     * @return FractalCollection|FractalAlias|null
     */
    public function resource($data)
    {
        if ($data instanceof Collection) {
            $this->resource = $this->transformCollection($data);
        } elseif ($data instanceof AbstractPaginator) {
            $this->resource = $this->transformPaginator($data);
        } else {
            $this->resource = $this->transformItem($data);
        }

        return $this->resource;
    }
}
